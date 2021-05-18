#!/bin/bash
VERSION=1.0
VERBOSE=0
PROGNAME=`/usr/bin/basename $0`
PROGPATH=`echo $0 | /bin/sed -e 's,[\\/][^\\/][^\\/]*$,,'`
. $PROGPATH/utils.sh

function printHelp() {
echo
echo "Usage: $PROGNAME [-v] [-V] [-h] [-L]"
echo
echo " -v verbose output"
echo " -V Version"
echo " -h this help"
echo " -L License"
echo " -w warning"
echo " -c critical"
echo
echo " This plugin checks the number of files in /tmp"
echo
}
function printVersion() {
    echo
    echo "$PROGNAME Version $VERSION"
    echo
}

function checkOptions() {
    while getopts "hvVc:w:" OPTIONS $@; do
            case $OPTIONS in
                c) CRITICAL=$OPTARG
                   ;;
                w) WARNING=$OPTARG
                   ;;
                h) printHelp
                   exit $STATE_UNKNOWN
                   ;;
                v) VERBOSE=1
                   ;;
                V) printVersion
                   exit $STATE_UNKNOWN
                   ;;
                ?) printHelp
                   exit $STATE_UNKNOWN
                   ;;
            esac
    done
}
checkOptions $@

# Jetzt werden die Dateien in /tmp gezählt und mit den Schwellwerten verglichen.

if test `find "/opt/viessmann/data0.tmp" -mmin +3`
then
	echo -n "WARNING - Alter $(( $(date +"%s") - $(stat -c "%Y" /opt/viessmann/data0.tmp) ))s"
	exitCode=1
else
	if test `find "/opt/viessmann/data0.tmp" -mmin +20`
	then
		echo -n "ERROR - Dateialter $(( $(date +"%s") - $(stat -c "%Y" /opt/viessmann/data0.tmp) ))s"
		exitCode=2
	else
		echo -n "OK - Dateialter $(( $(date +"%s") - $(stat -c "%Y" /opt/viessmann/data0.tmp) ))s" 
		exitCode=0
  	fi
fi

echo -n " - "
cat /opt/viessmann/data0.txt
echo " dateialter=$(( $(date +"%s") - $(stat -c "%Y" /opt/viessmann/data0.tmp) ))"
exit $exitCode

