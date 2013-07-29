#!/bin/bash
#echo "This scripts does Stackato setup related to filesystem."
FS=$STACKATO_FILESYSTEM

if [ -e $FS/images ]; then
    echo "Filesystem is already configured"

    echo "Symlinking LocalSettings.php to proper location ..."
    cp $FS/LocalSettings.php LocalSettings.php
    
    echo "All Done!"
else
    echo "Filesystem is NOT configured. Setting up ..."

    # create folders in the shared filesystem
    mkdir -p $FS/images
    mkdir -p $FS/cache
    mkdir -p $FS/logs

    echo "Migrating data to shared filesystem ..."
    cp images/.htaccess $FS/images
    cp cache/.htaccess $FS/cache

    echo "Symlink to folders in shared filesystem ..."
    rm -r -f images
    rm -r -f cache
    rm -r -f logs
    ln -s $FS/images images
    ln -s $FS/cache cache
    ln -s $FS/logs logs

    echo "All Done!"
fi

if [ -e $FS/LocalSettings.php ]; then
    echo "Filesystem LocalSettings.php Exists. Symlink it ..."
    ln -s $FS/LocalSettings.php LocalSettings.php
fi

echo "Moving .htaccess file into place"
mv .htaccess_stackato .htaccess
