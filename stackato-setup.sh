#!/bin/bash
#echo "This scripts does Stackato setup related to filesystem."

if [ -d "$STACKATO_FILESYSTEM/images" ]; then
    echo "Filesystem is already configured"

    echo "Symlinking LocalSettings.php to proper location ..."
    cp $STACKATO_FILESYSTEM/LocalSettings.php LocalSettings.php
    
    echo "All Done!"
else
    echo "Filesystem is NOT configured. Setting up ..."

    # create folders in the shared filesystem
    mkdir -p $STACKATO_FILESYSTEM/images
    mkdir -p $STACKATO_FILESYSTEM/cache
    mkdir -p $STACKATO_FILESYSTEM/logs

    echo "Migrating data to shared filesystem ..."
    cp images/.htaccess $STACKATO_FILESYSTEM/images
    cp cache/.htaccess $STACKATO_FILESYSTEM/cache

    echo "Symlink to folders in shared filesystem ..."
    rm -r -f images
    rm -r -f cache
    rm -r -f logs
    ln -s $STACKATO_FILESYSTEM/images images
    ln -s $STACKATO_FILESYSTEM/cache cache
    ln -s $STACKATO_FILESYSTEM/logs logs

    echo "All Done!"
fi

if [ -e "$STACKATO_FILESYSTEM/LocalSettings.php" ]; then
    echo "Filesystem LocalSettings.php Exists. Symlink it ..."
    ln -s $STACKATO_FILESYSTEM/LocalSettings.php LocalSettings.php
fi

echo "Moving .htaccess file into place"
mv .htaccess_stackato .htaccess
