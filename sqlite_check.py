import sqlite3
c = sqlite3.connect(r"D:\SponsorMatch\database\database.sqlite")
rows = c.execute("select name from sqlite_master where type='table' order by name").fetchall()
print("tabel:", [r[0] for r in rows])
