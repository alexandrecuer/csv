# emoncmsCSVmodule
an EmonCMS module to import feeds from csv files

## install
```
cd /var/www/emoncms/Modules
git clone https://github.com/emoncms/csv.git
```
Create directory named csv_files in /home/pi/data

You will have to transfer your csv files manually inside, using WinSCP if you are on windows

## csv files structure

First line has to contain all the feed names

First column has to contain the timestamps, format : DD/MM/YYYY - HH:MM:SS (check your timezone)

Please note you have to provide data recorded at a fixed interval

delimiter ``;``
