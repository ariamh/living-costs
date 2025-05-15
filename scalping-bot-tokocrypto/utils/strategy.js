import { EMA, RSI } from "technicalindicators";

export function calculateIndicators(
    closes,
    shortPeriod,
    longPeriod,
    rsiPeriod,
) {
    if (closes.length < Math.max(shortPeriod, longPeriod, rsiPeriod)) {
        throw new Error("Data candle tidak cukup untuk kalkulasi indikator.");
    }

    const emaShort = EMA.calculate({ period: shortPeriod, values: closes });
    const emaLong = EMA.calculate({ period: longPeriod, values: closes });
    const rsi = RSI.calculate({ period: rsiPeriod, values: closes });

    const lastEmaShort = emaShort.at(-1);
    const lastEmaLong = emaLong.at(-1);
    const lastRsi = rsi.at(-1);

    if ([lastEmaShort, lastEmaLong, lastRsi].some((v) => isNaN(v))) {
        throw new Error("Nilai indikator tidak valid (NaN).");
    }

    return {
        emaShort: lastEmaShort,
        emaLong: lastEmaLong,
        rsi: lastRsi,
    };
}

export function checkSignal(
    { emaShort, emaLong, rsi },
    rsiBuyThreshold,
    rsiSellThreshold,
) {
    const buy = emaShort > emaLong && rsi < Number(rsiBuyThreshold);
    const sell = emaShort < emaLong && rsi > Number(rsiSellThreshold);
    return { buy, sell };
}
