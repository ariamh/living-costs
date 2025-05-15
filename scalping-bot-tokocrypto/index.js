/**
 * Bot scalping otomatis untuk Tokocrypto
 * Menggunakan strategi kombinasi EMA dan RSI
 * Mendukung trailing stop, cut loss, cooldown, dan notifikasi Discord
 */
import dotenv from "dotenv";
import ccxt from "ccxt";
import fetch from "node-fetch";
import fs, { appendFileSync } from "fs";
import { calculateIndicators, checkSignal } from "./utils/strategy.js";

/**
 * Inisialisasi konfigurasi environment (.env)
 * agar variabel-variabel seperti API key dan setting strategi
 * bisa diakses melalui process.env
 */
dotenv.config();

// ===================== Konfigurasi Bot dari ENV =====================
/**
 * Menentukan pasangan trading (misalnya ETH/USDT) dari konfigurasi environment.
 * Variabel ini digunakan di seluruh proses trading.
 */
const symbol = process.env.SYMBOL || "ETH/USDT";
const [baseAsset, quoteAsset] = symbol.split("/");
const rsiBuyThreshold = Number(process.env.RSI_BUY_THRESHOLD) || 40;
const rsiSellThreshold = Number(process.env.RSI_SELL_THRESHOLD) || 60;
const emaShortPeriod = Number(process.env.EMA_SHORT_PERIOD) || 9;
const emaLongPeriod = Number(process.env.EMA_LONG_PERIOD) || 21;
const rsiPeriod = Number(process.env.RSI_PERIOD) || 14;
const cooldownSeconds = Number(process.env.COOLDOWN_SECONDS) || 60;
const notifyDiscord = process.env.NOTIFY_DISCORD === "true";
const discordWebhookUrl = process.env.DISCORD_WEBHOOK_URL;
const enableTrade = process.env.ENABLE_TRADE === "true";
const minBuyUsdt = Number(process.env.MIN_BUY_USDT) || 5;
const maxBuyUsdt = Number(process.env.MAX_BUY_USDT) || 20;
const minAssetBalance = Number(process.env.MIN_ASSET_BALANCE) || 0.001;
const minProfitPercent = Number(process.env.MIN_PROFIT_PERCENT) || 0.5;
const strategyMode = process.env.STRATEGY_MODE || "TECHNICAL";
const cutLossPercent = Number(process.env.CUT_LOSS_PERCENT) || -2;
const baseTrailingPercent = Number(process.env.TRAILING_STOP_PERCENT) || 0.5;
const maxBuyPerTrend = Number(process.env.MAX_BUY_PER_TREND) || 2;

// ===================== Variabel Internal =====================
let trailingHighPrice = null;
let currentTrend = null;
let buyCountThisTrend = 0;

/**
 * Fungsi logging dengan format waktu lokal (Asia/Jakarta),
 * digunakan untuk memberi tahu status bot secara real-time di console.
 */
function logNow(msg) {
  const now = new Date().toLocaleString("id-ID", { timeZone: "Asia/Jakarta" });
  console.log(`[${now}] ${msg}`);
}

/**
 * Membaca harga beli terakhir dari file storage lokal
 * sebagai referensi untuk logika SELL.
 */
function loadLastBuyPrice() {
  try {
    const data = fs.readFileSync("storage.json", "utf8");
    const parsed = JSON.parse(data);
    return parsed.lastBuyPrice || null;
  } catch (err) {
    return null;
  }
}

/**
 * Menyimpan harga beli terakhir untuk referensi SELL
 */
function saveLastBuyPrice(price) {
  const data = { lastBuyPrice: price };
  fs.writeFileSync("storage.json", JSON.stringify(data, null, 2));
}

/**
 * Menyimpan transaksi dalam bentuk CSV ke trade-history.csv
 */
function logTradeToCSV(
  type,
  symbol,
  amount,
  price,
  profit,
  reason,
  fee = 0,
  before = 0,
  after = 0,
  mode = "TECHNICAL",
  orderType = "MARKET",
) {
  const now = new Date().toISOString();
  const total = (amount * price).toFixed(2);
  const line = `${now},${type},${symbol},${amount},${price},${profit || 0},${reason || "-"},${fee},${before},${after},${mode},${orderType},${total}\n`;

  const fileExists = fs.existsSync("trade-history.csv");
  if (!fileExists) {
    const header =
      "timestamp,type,symbol,amount,price,profit,reason,fee,before,after,mode,order_type,total\n";
    fs.writeFileSync("trade-history.csv", header);
  }

  appendFileSync("trade-history.csv", line);
}

/**
 * Format pesan untuk Discord atau log terminal
 */
function formatTradeMessage({
  type,
  symbol,
  price,
  amount,
  baseAsset,
  usdtBefore,
  usdtAfter,
  fee,
  rsi = null,
  profit = null,
  reason = "-",
}) {
  const lines = [
    `✅ [TRADE] ${type.toUpperCase()} ORDER PLACED${reason !== "-" ? ` (${reason})` : ""}`,
    `Symbol: ${symbol}`,
    `Price: $${price}`,
    `${type === "BUY" ? "Buy Amount" : "Amount"}: ${amount} ${baseAsset}`,
    `USDT Before: ${usdtBefore} → After: ${usdtAfter}`,
    `Estimated Fee: ~${fee} ${baseAsset}`,
  ];
  if (rsi !== null) lines.push(`RSI: ${rsi}`);
  if (profit !== null) lines.push(`Profit: ${profit}%`);
  return lines.join("\n");
}

let lastTradeTime = 0;
let lastBuyPrice = loadLastBuyPrice();

/**
 * Inisialisasi objek exchange untuk koneksi ke Tokocrypto via ccxt
 */
const exchange = new ccxt.tokocrypto({
  apiKey: process.env.TOKOCRYPTO_API_KEY,
  secret: process.env.TOKOCRYPTO_SECRET_KEY,
  enableRateLimit: true,
  options: {
    createMarketBuyOrderRequiresPrice: false,
  },
});

/**
 * Mengirim pesan notifikasi ke channel Discord.
 * Berguna untuk pemantauan jarak jauh setiap aksi bot.
 */
async function sendDiscordNotification(message) {
  if (!notifyDiscord || !discordWebhookUrl) return;
  try {
    const res = await fetch(discordWebhookUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ content: message }),
    });
    if (!res.ok) {
      const text = await res.text();
      console.error(`❌ Discord Error [${res.status}]:`, text);
    } else {
      console.log("✅ Notifikasi dikirim ke Discord");
    }
  } catch (err) {
    console.error("❌ Gagal kirim ke Discord:", err.message);
  }
}

/**
 * Mengatur nilai trailing stop dinamis berdasarkan persentase profit.
 * Jika profit tinggi, trailing stop lebih longgar (0.6–0.8%).
 * Jika profit rendah, gunakan nilai default dari konfigurasi.
 */
function getDynamicTrailingStopPercent(price) {
  if (!lastBuyPrice) return baseTrailingPercent;
  const profit = ((price - lastBuyPrice) / lastBuyPrice) * 100;
  if (profit > 3) return 0.8;
  if (profit > 2) return 0.6;
  return baseTrailingPercent;
}

/**
 * Mengevaluasi tren saat ini berdasarkan posisi EMA.
 * Jika terjadi pergantian tren (UPTREND ↔ DOWNTREND),
 * maka jumlah pembelian per tren akan di-reset ke 0.
 */
function resetTrend(price, emaShort, emaLong) {
  const trend = emaShort > emaLong ? "UPTREND" : "DOWNTREND";
  if (trend !== currentTrend) {
    currentTrend = trend;
    buyCountThisTrend = 0;
    logNow(`📉 Tren berubah ke: ${trend}, reset jumlah BUY ke 0.`);
  }
  return trend;
}

/**
 * Menentukan apakah sinyal BUY valid berdasarkan:
 * - sinyal dari indikator (signal.buy)
 * - EMA pendek harus di atas EMA panjang
 * - RSI harus di bawah ambang beli
 */
function isBuyValid(signal, emaShort, emaLong, rsi) {
  return signal.buy && emaShort > emaLong && rsi < rsiBuyThreshold;
}

/**
 * Mengecek apakah jumlah BUY dalam tren saat ini belum melebihi batas maksimal.
 * Jika melebihi, log akan dicetak dan tidak ada order dilakukan.
 */
function limitBuyInTrend() {
  if (buyCountThisTrend >= maxBuyPerTrend) {
    logNow(`⚠️ Batas maksimum BUY dalam tren tercapai (${maxBuyPerTrend})`);
    return false;
  }
  return true;
}

/**
 * Menghitung jumlah koin yang bisa dibeli berdasarkan saldo USDT dan batasan minimal/maksimal.
 * Jika saldo tidak mencukupi, akan mengembalikan validasi false dan alasan pembatalan.
 */
function calculateBuyAmount(usdtBalance, price) {
  let usdtToUse = Math.min(usdtBalance, maxBuyUsdt);
  if (usdtToUse < minBuyUsdt) {
    return {
      valid: false,
      reason: `Jumlah pembelian hanya $${usdtToUse.toFixed(2)}, kurang dari minimum ${minBuyUsdt} USDT (syarat Tokocrypto).`,
    };
  }
  const amount = parseFloat((usdtToUse / price).toFixed(5));
  const estimatedFee = amount * 0.001;
  return {
    valid: true,
    amount,
    usdtToUse,
    estimatedFee,
  };
}

/**
 * Mengambil data harga OHLCV dari exchange berdasarkan timeframe.
 * Data ini digunakan untuk menghitung indikator teknikal.
 */
async function getMarketData(symbol, timeframe = "1m", limit = 50) {
  const candles = await exchange.fetchOHLCV(
    symbol,
    timeframe,
    undefined,
    limit,
  );
  return candles.map((c) => c[4]); // ambil harga close
}

/**
 * Fungsi utama bot yang berjalan periodik:
 * - Mengecek cooldown
 * - Mengambil data market & menghitung indikator
 * - Mengevaluasi sinyal BUY/SELL
 * - Mengeksekusi order dan log transaksi
 */
async function runBot() {
  try {
    const now = Date.now();
    const remainingCooldown =
      (lastTradeTime + cooldownSeconds * 1000 - now) / 1000;
    if (remainingCooldown > 0) {
      logNow(
        `⏳ Cooldown aktif, sisa waktu: ${Math.ceil(remainingCooldown)} detik`,
      );
      return;
    }

    const prices = await getMarketData(symbol);
    const indicators = calculateIndicators(
      prices,
      emaShortPeriod,
      emaLongPeriod,
      rsiPeriod,
    );
    const signal = checkSignal(indicators, rsiBuyThreshold, rsiSellThreshold);
    const { emaShort, emaLong, rsi } = indicators;
    resetTrend(prices.at(-1), emaShort, emaLong);

    logNow(
      `${symbol} | EMA${emaShortPeriod}: ${emaShort.toFixed(2)}, EMA${emaLongPeriod}: ${emaLong.toFixed(2)}, RSI: ${rsi.toFixed(2)}`,
    );

    const balance = await exchange.fetchBalance();
    const usdtBalance = balance.free[quoteAsset] || 0;
    const assetBalance = balance.free[baseAsset] || 0;
    const ticker = await exchange.fetchTicker(symbol);
    const price = ticker.bid;
    const lastPrice = ticker.last;

    // === BUY LOGIC ===
    if (isBuyValid(signal, emaShort, emaLong, rsi) && limitBuyInTrend()) {
      const buyInfo = calculateBuyAmount(usdtBalance, price);
      if (!buyInfo.valid) {
        const warnMsg = `⚠️ BUY dibatalkan: ${buyInfo.reason}`;
        logNow(warnMsg);
        await sendDiscordNotification(warnMsg);
        return;
      }

      const { amount, usdtToUse, estimatedFee } = buyInfo;

      try {
        if (enableTrade) {
          await exchange.createMarketBuyOrder(symbol, usdtToUse);
          const newBalance = await exchange.fetchBalance();
          const usdtAfter = newBalance.free[quoteAsset] || 0;

          lastBuyPrice = price;
          saveLastBuyPrice(price);
          lastTradeTime = now;
          trailingHighPrice = price;
          buyCountThisTrend++;

          const msg = formatTradeMessage({
            type: "BUY",
            symbol,
            price,
            amount,
            baseAsset,
            usdtBefore: usdtBalance.toFixed(2),
            usdtAfter: usdtAfter.toFixed(2),
            fee: estimatedFee.toFixed(5),
            rsi: rsi.toFixed(2),
          });

          logNow(msg);
          await sendDiscordNotification(msg);
          logTradeToCSV(
            "BUY",
            symbol,
            amount,
            price,
            0,
            "BUY",
            estimatedFee.toFixed(5),
            usdtBalance.toFixed(2),
            usdtAfter.toFixed(2),
            strategyMode,
            "MARKET",
          );
        } else {
          const msg = `🟡 [SIMULASI] BUY Signal
Price: $${price}, USDT: ${usdtToUse.toFixed(2)}`;
          logNow(msg);
          await sendDiscordNotification(msg);
        }
      } catch (error) {
        const errorMsg = `❌ Gagal melakukan BUY: ${error.message}`;
        logNow(errorMsg);
        await sendDiscordNotification(errorMsg);
      }
      return;
    }

    // === SELL LOGIC ===
    if (assetBalance >= minAssetBalance && lastBuyPrice) {
      const profitPercent = ((price - lastBuyPrice) / lastBuyPrice) * 100;
      const cutLossTriggered = profitPercent <= cutLossPercent;

      if (!trailingHighPrice || price > trailingHighPrice) {
        trailingHighPrice = price;
      }
      const dynamicTrailing = getDynamicTrailingStopPercent(price);
      const trailingStopTriggered =
        trailingHighPrice &&
        price <= trailingHighPrice * (1 - dynamicTrailing / 100);

      const isTechnicalSell = signal.sell && profitPercent >= minProfitPercent;
      const isPriceSell =
        lastPrice >= lastBuyPrice * (1 + minProfitPercent / 100);

      if (
        cutLossTriggered ||
        trailingStopTriggered ||
        (strategyMode === "TECHNICAL" && isTechnicalSell) ||
        (strategyMode === "PRICE" && isPriceSell)
      ) {
        const feeBuffer = 0.999;
        const rawAmount = assetBalance * feeBuffer;
        const amount = parseFloat(rawAmount.toFixed(8));
        const estimatedFee = amount * 0.001;
        const usdtBefore = usdtBalance;

        if (enableTrade) {
          await exchange.createMarketSellOrder(symbol, amount);
          const newBalance = await exchange.fetchBalance();
          const usdtAfter = newBalance.free[quoteAsset] || 0;

          lastTradeTime = now;
          trailingHighPrice = null;

          const reason = cutLossTriggered
            ? "CUT LOSS"
            : trailingStopTriggered
              ? "TRAILING STOP"
              : strategyMode;

          const msg = formatTradeMessage({
            type: "SELL",
            symbol,
            price,
            amount,
            baseAsset,
            usdtBefore: usdtBefore.toFixed(2),
            usdtAfter: usdtAfter.toFixed(2),
            fee: estimatedFee.toFixed(5),
            profit: profitPercent.toFixed(2),
            reason,
          });

          logNow(msg);
          await sendDiscordNotification(msg);
          logTradeToCSV(
            "SELL",
            symbol,
            amount,
            price,
            profitPercent.toFixed(2),
            reason,
            estimatedFee.toFixed(5),
            usdtBefore.toFixed(2),
            usdtAfter.toFixed(2),
            strategyMode,
            "MARKET",
          );

          if (cutLossTriggered) {
            await sendDiscordNotification(
              `🚨 CUT LOSS TRIGGERED! Harga: $${price}, Kerugian: ${profitPercent.toFixed(2)}%`,
            );
          }
        } else {
          const msg = `🟡 [SIMULASI] SELL Signal
Price: $${price}, Profit: ${profitPercent.toFixed(2)}%`;
          logNow(msg);
          await sendDiscordNotification(msg);
        }
        return;
      } else {
        logNow(
          `⚪ Tidak ada sinyal SELL (${strategyMode}) — Profit: ${profitPercent.toFixed(2)}%`,
        );
      }
    }

    logNow("⚪ Tidak ada sinyal aksi beli/jual.\n");
  } catch (err) {
    console.error("❌ Bot Error:", err.message);
    await sendDiscordNotification(`❌ Bot Error: ${err.message}`);
  }
}

console.log("🤖 Scalping Bot Tokocrypto AKTIF");
console.log(
  `📊 Pair: ${symbol} | EMA: ${emaShortPeriod}/${emaLongPeriod} | RSI: ${rsiPeriod}`,
);
console.log(`🚀 MODE: ${enableTrade ? "AUTO TRADE ✅" : "MONITORING ONLY ❌"}`);
console.log(`📈 STRATEGY: ${strategyMode}`);
console.log(`⏱️ Interval: 30 detik | Cooldown: ${cooldownSeconds} detik\n`);
console.log(
  `📌 Harga beli terakhir (persisted): ${lastBuyPrice || "Belum tersedia"}\n`,
);

setInterval(runBot, 30000);
