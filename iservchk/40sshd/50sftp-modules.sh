#!/bin/sh

. /usr/lib/iserv/cfg

if [ -d "/sftp-chroot/var/lib/iserv/vplan" ]
then
  echo "ChPerm 0751 root:$GrpVplanAdmin /sftp-chroot/var/lib/iserv/vplan"
fi

if [ -d "/sftp-chroot/var/lib/iserv/infodisplay" ]
then
  echo "ChPerm 0751 root:infobildschirm /sftp-chroot/var/lib/iserv/infodisplay"
fi

echo
