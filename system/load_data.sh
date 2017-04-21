#!/bin/bash

cd "$(dirname "$0")"
DATE=$(date --date="yesterday" +"%Y-%m-%d")

set -- $(getopt -uo dh: --long --unquoted date,help: -- "$@")

while true; do
    case "$1" in
        ( -d | --date ) DATE=$2; shift ;;
        * ) break ;;
    esac
done

php Console/PackManager.php $DATE &&

# Load data to mongo
php Console/parser.php TRN $DATE &&      # Transaction
php Console/parser.php SEC $DATE &&      # Security
php Console/parser.php PRI $DATE &&      # Price
php Console/parser.php POS $DATE &&      # Position
php Console/parser.php TXT $DATE &&      # Realized
php Console/parser.php CBL $DATE &&      # Unrealized
php Console/parser.php CBP $DATE &&      # Unrealized

# Load data from mongo to mysql
php Console/normalizer.php SEC $DATE &&  # Security
php Console/normalizer.php TRN $DATE &&  # Transaction
php Console/normalizer.php POS $DATE &&  # Position
php Console/normalizer.php TXT $DATE &&  # Realized
php Console/normalizer.php CBL $DATE &&  # Unrealized
php Console/normalizer.php CBP $DATE &&  # Unrealized
php Console/normalizer.php TWR $DATE     # Twr caculator