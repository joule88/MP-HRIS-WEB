$path = "c:\xampp\htdocs\mpg-hris\database\base_schema.sql"
$content = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::Unicode)

# Hapus blok DROP TABLE + CREATE TABLE untuk tabel 'migrations' (biar Laravel yang manage)
$pattern = "(?s)--\s*\n--\s*Table structure for table ``migrations``\s*\n--\s*\n\nDROP TABLE IF EXISTS ``migrations``.*?CREATE TABLE ``migrations``.*?;\n/\*.*?\*/;\n"
$content = [regex]::Replace($content, $pattern, "")

# Tulis ulang sebagai UTF-8 tanpa BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($path, $content, $utf8NoBom)

Write-Host "Done: base_schema.sql converted to UTF-8 and migrations table removed."
