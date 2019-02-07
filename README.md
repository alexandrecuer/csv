# emoncmsCSVmodule
an EmonCMS module to import feeds from csv files

in development

TODO : implement the data transfer from csv to PHPFINA feed as background work for a shell worker being run by the EmoncCMS python service-runner

## install
```
cd /var/www/emoncms/Modules
git clone https://github.com/alexandrecuer/csv.git
```
Create directory named csv_files in /home/pi/data

You will have to transfer your csv files manually inside, using WinSCP if you are on windows

## csv files structure

First line has to contain all the feed names

First column has to contain the timestamps, format : DD/MM/YYYY - HH:MM:SS (check your timezone)

Please note you have to provide data recorded at a fixed interval

delimiter ``;``
