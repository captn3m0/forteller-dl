# forteller-dl

Download media from the https://www.fortellergames.com/ store without installing the application. The script behaves the same way as the application, and it can only download media that you will have access to, so please purchase the media from [fortellergames.com](https://www.fortellergames.com) before you run this script.

Tested mainly against "Gloomhaven: Jaws of the Lion".

## Why?

I didn't like the application UX, so wrote this script instead to download the files. The app stretches the play/pause button on my iPhone SE and it looked very ugly. Plus, I can uninstall the app now and [play the files anywhere](www.defectivebydesign.org). Kudos for Forteller for having a clean API and no DRM.

## How to use

First configure your credentials in the `.env` file. 

`cp .env.sample .env`

Edit the .env file with your correct credentials.

### Running using Docker

```sh
# Set SKU to one of `ceph_gh`,`ceph_jaws`,`suc_mid1`,`ceph_fh`,`skg_iso`
docker run -it --init --volume "$HOME/Downloads:/downloads" --env-file .env ghcr.io/captn3m0/forteller-dl:main [SKU] /downloads
```

### Running using local PHP

You'll need php, php-curl installed, and replace {SKU} with a valid SKU (One of `ceph_gh`,`ceph_jaws`,`suc_mid1`,`ceph_fh`,`skg_iso`, but not all are available right now).

```
git clone https://github.com/captn3m0/forteller-dl.git
cd forteller-dl
set -a
source .env
set +a
php run.php {SKU} [/optional/path/to/output/dir]
```

## TODO

The script is functional enough for me, so this will likely never get done. But ideas:

- [x] Run with Docker
- [ ] Cleanup code
- [ ] API Documentation?
- [ ] Tag the MP3 files as they are saved

## License

Licensed under the [MIT License](https://nemo.mit-license.org/). See LICENSE file for details.