Making a CoughPHP Release
=========================

cp -rp coughphp-trunk-svn coughphp-1.1-RC2
cd coughphp-1.1-RC2
cleansvn
rm -f CoughPHP.tmproj
rm -rf design
rm -rf simpletest
rm -rf docs/developer
rm -rf scripts/make_release
find . -type d -name tests -exec rm -rf {} \;
cd ..
tar -czf coughphp-1.1-RC2.tar.gz coughphp-1.1-RC2
