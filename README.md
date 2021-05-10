# foreteller-dl

Download media from the https://www.foretellergames.com/ store without installing the application.

I didn't like the application UX, so wrote this hacky script instead.

Tested against "Gloomhaven: Jaws of the Lion", but it does seem to work on others as well, as long as you've bought it from the store.

## How to use

You'll need php, php-curl installed, and replace {SKU} with a valid SKU (One of `ceph_gh`,`ceph_jaws`,`suc_mid1`,`ceph_fh`,`skg_iso`, but not all are available right now).

```
git clone https://github.com/captn3m0/foreteller-dl.git
cd foreteller-dl
cp config.sample.php config.php
// Edit the config.php file to put your credentials
php run.php {SKU}
```

## License

Licensed under the [MIT License](https://nemo.mit-license.org/). See LICENSE file for details.