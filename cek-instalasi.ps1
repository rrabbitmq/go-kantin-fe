# Cek instalasi Go Kantin.
# Jalankan dari folder go-kantin-fe (yang berisi folder 'frontend' dan 'backend'):
#   powershell -ExecutionPolicy Bypass -File .\cek-instalasi.ps1
#
# Hasil:
#   OK     = file sama persis dengan versi paket
#   BEDA   = file ada, tetapi isinya berbeda (belum ditimpa, atau sudah kamu ubah sendiri)
#   HILANG = file belum ada

$daftar = @'
backend/app/Core/Media.php|03b9b9722dc97bf5c6460936ac41e2c5782ee5e66e214063afb3db9aef0a9006
backend/app/Models/KategoriPenjual.php|d8f2ea2a8295dba566b2281f38af1ba6b035baecf7bbdfb439a407244a61fd5d
backend/app/Models/Keranjang.php|b7c8c5c437e385adee8cf9e971ad82914b1ae2f8e378f5ddba81b35063a0190f
backend/app/Models/Menu.php|f2edc3dcda4d4bed3a5edec5640bfeed2c67d7b9cf00717dee121124d4842585
backend/app/Models/Penjual.php|969934a92c0fadfb40e22c9982b0690ab99cd1149b45f1cd606d34398884cca1
backend/app/Models/Produk.php|612c7d4d81bd8cd2c54ab033bb3605de10e7f07c65e1c2324e374f1f314eb41c
backend/app/Models/Transaksi.php|3c35f241a2d82599ebcfd5d0cdf4b627ee485dbc9613fd1c9f9cc88f7e989202
backend/app/Services/CheckoutService.php|70c457a2acf57c5e974ed2d35f00afefa4ade198d80aa01f6c476cc64a05a6a7
backend/app/Services/MenuService.php|2696258bfbf1a315c852a8ac1eaadaa0ae67cd965c2e7704e7076d6f31454456
backend/app/Services/PesananService.php|e5ccb6b86c838443b0ad79981b3f3e2024da9976b1792806fcc97a5d453e1987
backend/public/index.php|72fc4d9b15a737a6ff8e1397d8a645e4a01e70d3690cca6a6e70f9345959eec9
backend/routes/api/checkout.php|6a58a213edd8c2c6599d0c1468712f618814216c896f69738880c7704e9511a0
backend/routes/api/kategori-penjual.php|c00b70edc3c441c33ebe8627c19ee10d23493c7ba88a5b2735e74ec8ecad7691
backend/routes/api/penjual-dashboard.php|b48c9b69f580f3aca3b2ea1810930b73f82afdfed5eef4a3c21e9fe98e833cdc
backend/routes/api/penjual-menu.php|8a5305c45679fd14170fd48106aaca0aa6b024f0995e61e4d25fd184cd1e5e74
backend/routes/api/penjual-pesanan.php|8d5b3c2dcaf459e1fbf74eb2a96e50d8d5460bc922becbec53343ced26553f22
backend/routes/api/penjual-stok.php|276bda4473089e3f304788eb5ff059699952b96ef50a6ce2f76b016816f84673
backend/routes/api/register.php|8339d2d107f30dc76527cf6c7c12970244a1df113297f80424c6a9b63e08d1ff
database/006_metode_pembayaran.sql|e5ad921d31a6bba3d5c84da745b21b77d9a170efb9bab9373f254477e6faa4f8
frontend/app/Controllers/AdminController.php|87ee1c50e79c74f46a71b9cfc62a36152e591b34e26f6918bf91b5252a2d5284
frontend/app/Controllers/AuthController.php|c9da0f629e3f38a5b999017b92a81c1e3d7cb47df2792d93b30d120cc006c031
frontend/app/Controllers/CartController.php|df7f7e21126b36966b2da399e98cb76e62ded11b6dfe8b8d4f049a96171c8725
frontend/app/Controllers/CheckoutController.php|754fd71e8e0cd89f82b5aed23e65f1031281a5d1ff25b51ebd95bb201f241cef
frontend/app/Core/ApiClient.php|e3ed4d7903686cfd098a6bcba73dceccada0e4ab60ab93c4b4ee88d0a007e014
frontend/app/Models/KategoriPenjual.php|7eae44c8352685fbda76aaa0aaa5762f3ae115aa12e75512aca8b0f818b018a6
frontend/app/Models/Keranjang.php|1dff3cd1c8c4fd169a153575282be48a7307800675e96ef4ebfb12df07682d58
frontend/app/Models/Penjual.php|efc0092f1abe79df28faebfac60a9f813f309e3ad11259d56da20b90e1b5999f
frontend/app/Models/Transaksi.php|1f04338c88e1b780567f7fa898cfeb2dc1c2da6be464d60b1b69c474fd2451d9
frontend/app/Views/admin/antrian.php|bed8c7061a4f001339da9b6dacba9b5441fbd77cac9bfcc71250bd8ae9a8fe00
frontend/app/Views/admin/dashboard.php|1a36b88249ccdfcb0274a9575476bdb9752bce2362e593183980b97d691d2097
frontend/app/Views/admin/menu.php|960d3d2df8581e10c238bb01112263c5b5c5444248a135147029b16547b86110
frontend/app/Views/auth/login.php|d8b009ca260667b4b7b4ea7c51194175a4341adf23a88e6ea0432ce6431a598e
frontend/app/Views/auth/register-penjual.php|b5cebc2629ec41beec56f38824a16fd7939f93ad86d5fb82a01eb6d7f989b66a
frontend/app/Views/layouts/admin.php|73c9e31df16f0c0ff9562b1373346cf8cfb646ca63078beddfd30f04f08417ed
frontend/app/Views/layouts/main.php|0a04b841b9f91a00026bd9e889ba605e75e83542fb0843cc22165d5cfcfd145b
frontend/app/Views/pembeli/checkout.php|0d3cb81d0f8eb518cd6b6ea56a79f224ad074d794ea55fd03b19a267a8e9821b
frontend/app/Views/pembeli/detail-product.php|401dc1fe8d394100a8d6dd26d386331a423cb402f42190f100facdf7c976a436
frontend/public/admin/antrian.php|04aaf2287d7af2952dae3641412d9d4c1d84ccbe419f02128af82ac96c845300
frontend/public/admin/menu.php|791f89757dd9668c00760244538bf794d9640c19dbe13647040cf3c0cf52f886
frontend/public/auth/penjual/register.php|0ca85841db322325e00c4c20e77876be0ed5c163c4cd47a94936c4a6e8bd8cbb
frontend/public/js/admin-menu.js|fafb3651b799039428be77d9ca703715601ea0108888e4a7df18083ea136b28b
frontend/public/js/cart-action.js|a65cde1e788c7a483199b145c8787cc16e406f2689e52b5cd9c87c5b579a2f65
frontend/public/user/cart.php|216da26e72a135e4abab9516019f7ca71086ee5e8ff05e8ff17a43a067303a6d
'@ -split "`r?`n" | Where-Object { $_ -match '\|' }

function Get-HashNorm($path) {
    $t = [IO.File]::ReadAllText($path, [Text.Encoding]::UTF8) -replace "`r`n", "`n"
    $b = [Text.Encoding]::UTF8.GetBytes($t)
    $h = [Security.Cryptography.SHA256]::Create().ComputeHash($b)
    return ([BitConverter]::ToString($h) -replace '-', '').ToLower()
}

if (-not (Test-Path 'frontend') -or -not (Test-Path 'backend')) {
    Write-Host "Jalankan dari folder go-kantin-fe (yang berisi 'frontend' dan 'backend')." -ForegroundColor Red
    exit 1
}

$ok = 0; $beda = @(); $hilang = @()
foreach ($baris in $daftar) {
    $rel, $hash = $baris.Split('|')
    $path = Join-Path (Get-Location) ($rel -replace '/', '\')
    if (-not (Test-Path $path)) { $hilang += $rel; continue }
    if ((Get-HashNorm $path) -eq $hash) { $ok++ } else { $beda += $rel }
}

Write-Host ""
Write-Host "OK     : $ok file" -ForegroundColor Green
if ($beda.Count -gt 0) {
    Write-Host "BEDA   : $($beda.Count) file (isi berbeda dari paket)" -ForegroundColor Yellow
    $beda | ForEach-Object { Write-Host "   $_" -ForegroundColor Yellow }
}
if ($hilang.Count -gt 0) {
    Write-Host "HILANG : $($hilang.Count) file" -ForegroundColor Red
    $hilang | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
}
if ($beda.Count -eq 0 -and $hilang.Count -eq 0) {
    Write-Host "Semua file sudah sesuai." -ForegroundColor Green
}
