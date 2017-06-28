#!/bin/sh

. /usr/lib/iserv/cfg

if [ -d "/sftp-chroot/var/lib/iserv/vplan" ]
then
  echo "ChPerm 0751 root:$GrpVplanAdmin /sftp-chroot/var/lib/iserv/vplan"
fi
echo
