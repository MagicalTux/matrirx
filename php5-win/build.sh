#!/bin/sh
# Build PHP5.1 for linux

for foo in src/php-5.1-cvs-*.tar.bz2; do
	VERSION=`basename "$foo" .tar.bz2`
done

CONFIGURE=`cat src/php-configure.txt`

# First, extract sourcecode*
echo -n "Extracting $VERSION sourcecode..."
tar xjf "src/$VERSION.tar.bz2"
if [ $? != 0 ]; then
	echo "failed"
	exit 1
fi
echo "ok"

echo -n "Running configure..."
cd "$VERSION"
echo "$CONFIGURE" >configure.log
$CONFIGURE >>configure.log 2>&1
if [ $? != 0 ]; then
	echo "failed"
	tail configure.log
	exit 1
fi
echo "ok"

echo -n "Compiling php..."
make >make.log 2>&1
if [ $? != 0 ]; then
	echo "failed"
	tail make.log
	exit 1
fi
echo "ok"

echo -n "Symlinking..."
cd ..
rm -f php
ln -s "$VERSION/sapi/cli/php" ./php
echo "ok"

