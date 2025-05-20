<!DOCTYPE html>
<html>
<head>
    <title>Transaction Demo</title>
</head>
<body>
    <h1>Demo Transaction (Race Condition, Lock, Rollback)</h1>

    <form action="{{ url('/transaction/no-lock/1/1000') }}" method="GET" target="_blank">
        <button type="submit">➤ Tambah 1000 Tanpa Lock</button>
    </form>
    <br>

    <form action="{{ url('/transaction/with-lock/1/1000') }}" method="GET" target="_blank">
        <button type="submit">➤ Tambah 1000 Dengan Lock</button>
    </form>
    <br>

    <form action="{{ url('/transaction/rollback/1/1000') }}" method="GET" target="_blank">
        <button type="submit">➤ Simulasi Rollback</button>
    </form>

    <p style="margin-top: 30px; font-style: italic;">Pastikan ID transaksi 1 sudah ada di database.</p>
</body>
</html>
