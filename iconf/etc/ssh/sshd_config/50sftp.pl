#!/usr/bin/perl -CSDAL

use warnings;
use strict;
use IServ::Conf;
use IServ::DB;

my @GrpSSH = @{ $conf->{GrpSSH} };
my $has_root = 0;

print "\n";
print "# General rules for SFTP access\n\n";
print "# --- WARNING: DO NOT CHANGE THIS MATCH BLOCK BY HAND! --- \n\n";
print "# To exclude groups from this Match block, add them to GrpSSH\n".
  "# in iservcfg.\n\n";
print "# Exclude groups from GrpSSH and iserv-remote-support here to allow port\n".
  "# forwarding and shell access in general. GrpSSH users without shell are\n".
  "# limited to the sftp subsystem and chroot beyond (port forwarding is still\n".
  "# allowed).\n";
print "Match Group *,";

for (@GrpSSH)
{
  $has_root = 1 if $_ eq "root";
  print "!$_,";
}

if (not $has_root)
{
  print "!root,";
}

print "!iserv-remote-support\n";

print "ChrootDirectory /sftp-chroot\n";
print "X11Forwarding no\n";
print "AllowTcpForwarding no\n";
print "ForceCommand internal-sftp -P symlink -l INFO -d %d -u 0002\n\n";

my %listed_users;
my @limited_users;
for (@GrpSSH)
{
  my @users = IServ::DB::SelectCol "SELECT ActUser FROM members WHERE ActGrp = ?", $_;
  foreach my $user (@users)
  {
    next if defined $listed_users{$user};
    my @pwnam = getpwnam $user;
    next if not $pwnam[8] =~ /^(\/usr\/sbin\/nologin|\/bin\/false)/;
    next if defined $listed_users{$user};
    push @limited_users, $user;
    $listed_users{$user} = 1;
  }
}

if (@limited_users > 0)
{
  print "# Limit members of groups from GrpSSH without shell to chroot and sftp\n\n";
  print "# --- WARNING: DO NOT CHANGE THIS MATCH BLOCK BY HAND! --- \n\n";
  print "# To remove users which you just granted shell access from this block\n".
      "# run \"iservchk sshd\". The users also must have a membership in one\n".
      "# of the groups listed in the GrpSSH setting in iservfg.\n";
  my $users = join ",", @limited_users;
  print "Match User ".$users."\n";
  print "ChrootDirectory /sftp-chroot\n";
  print "ForceCommand internal-sftp -P symlink -l INFO -d %d -u 0002\n\n";
}
