# Simple Exporter App

This is a simple application that downloads a `jsonl` file from `S3` and exports the result inside the `storage/app` folder.

Built using `Lumen` as it's core, it uses `Nesbot\Carbon`, `Maatwebsite\Excel`, `Stolt\JsonLines` and `League\FlysystemAwsS3V3`.

## How to run

1. Clone this repo.
2. Run `install.sh` in your terminal, it will download & install composer for you.
3. Run `php artisan export` whenever you want to export the data.
4. You can see the output `csv` file inside the `storage/app` folder

It can be upgraded, i.e. sending email with the output file attached, another output format, etc.