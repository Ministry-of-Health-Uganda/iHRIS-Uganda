#!/bin/bash
#Exit on error
set -e
#set -x
#set -v

AWK=/usr/bin/awk
DCH=/usr/bin/dch
SED=/bin/sed
BZR=/usr/bin/bzr
GREP=/bin/grep

if [ $# -gt 0 ]; then
	while [ "$1" != "" ]; do
		SPACE=' '
		PACKAGES=$PACKAGES$SPACE$1
		shift
	done
else
	PACKAGES='i2ce ihris-common ihris-manage ihris-train textlayout ihris-qualify'
fi

cd ../../
IHRISHOME=`pwd`


for PACKAGE in $PACKAGES; do    
	for DIR in $IHRISHOME/$PACKAGE/packaging*; do
		cd $DIR
		./create-deb.sh
		echo "Set $DIR"
	done
done
