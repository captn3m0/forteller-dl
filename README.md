# foreteller-dl

Download media from the https://www.foretellergames.com/ store without installing the application. The script behaves the same way as the application, and it can only download media that you will have access to, so please purchase the media from [fortellergames.com](https://www.fortellergames.com) before you run this script.

Tested mainly against "Gloomhaven: Jaws of the Lion".

## Why?

I didn't like the application UX, so wrote this script instead to download the files. The app stretches the play/pause button on my iPhone SE and it looked very ugly. Plus, I can uninstall the app now and [play the files anywhere](www.defectivebydesign.org). Kudos for Foreteller for having a clean API and no DRM.

## How to use

You'll need php, php-curl installed, and replace {SKU} with a valid SKU (One of `ceph_gh`,`ceph_jaws`,`suc_mid1`,`ceph_fh`,`skg_iso`, but not all are available right now).

```
git clone https://github.com/captn3m0/foreteller-dl.git
cd foreteller-dl
cp config.sample.php config.php
// Edit the config.php file to put your credentials
php run.php {SKU}
```

## TODO

The script is functional enough for me, so this will likely never get done. But ideas:

- [ ] Run with Docker
- [ ] Cleanup code
- [ ] API Documentation?
- [ ] Tag the MP3 files as they are saved

## License

Licensed under the [MIT License](https://nemo.mit-license.org/). See LICENSE file for details.