The Poppy Seed Pets database contains emojis, and characters from other alphabets such as Japanese, Cyrillic, and more. Using simple `mysqldump` commands will NOT preserve these characters, resulting in a bad export.

When running `mysqldump`, **be sure to include `--default-character-set=utf8mb4`**. For example:

```
mysqldump --host=localhost --default-character-set=utf8mb4 --port=3306 --user=root -p poppyseedpets > FILE_NAME_TO_EXPORT_TO.sql
```

**When running this on Windows**, use Powershell, and run the following command BEFORE running `mysqldump`:

```powershell
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
```

