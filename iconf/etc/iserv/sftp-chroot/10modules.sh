#!/bin/sh

if [ -f "/var/lib/dpkg/info/iserv-plan.list" ]
then
  echo "# IServ Plan module"
  echo "/var/lib/iserv/vplan/files"
  echo
fi

if [ -f "/var/lib/dpkg/info/iserv-infodisplay.list" ]
then
  echo "# IServ Infodisplay module"
  echo "/var/lib/iserv/infodisplay"
  echo
fi
