# forteller-dl

Download media from the https://www.fortellergames.com/ store without installing the application. The script behaves the same way as the application, and it can only download media that you will have access to, so please purchase the media from [fortellergames.com](https://www.fortellergames.com) before you run this script.

Tested mainly against "Gloomhaven: Jaws of the Lion".

## Why?

I didn't like the application UX, so wrote this script instead to download the files. The app stretches the play/pause button on my iPhone SE and it looked very ugly. Plus, I can uninstall the app now and [play the files anywhere](https://www.defectivebydesign.org). Kudos for Forteller for having a clean API and no DRM.

## How to use

First configure your credentials in the `.env` file. 

`cp .env.sample .env`

Edit the .env file with your correct credentials.

## Getting your SKU

The SKU is the product code for each of the Forteller Titles. This is required for downloading the correct title.
This script authenticates using your credentials, so you can only download titles you have purchased already.

|SKU|Game|
---|---
`raven_aab`|[Above and Below](https://www.fortellergames.com/games/aboveandbelow)
`ceph_gh`|[Gloomhaven](https://www.fortellergames.com/games/gloomhaven)
`ceph_jaws`|[Jaws of the Lion](https://www.fortellergames.com/games/jawsofthelion)
`suc_mid1`|[Middara: Act 1](https://www.fortellergames.com/games/middara)
`ceph_fh`|[Frosthaven](https://www.fortellergames.com/games/frosthaven)
`skg_iso`|[The Isofarian Guard](https://www.fortellergames.com/games/theisofarianguard)

In the commands that follow, please replace SKU with the correct SKU from the left column.

### Running using Docker

Make sure you have configured your credentials as per the above.

#### Login to the GitHub Container Registry:

1. Create a new [Personal Access Token](https://github.com/settings/tokens/new?scopes=read:packages&description=Docker%20Login) with the `read:packages` scope.
2. Run `docker login ghcr.io`. Put your GitHub username as the username, and the token you generated above as the password.

#### Downloading files using Docker

Change `$HOME/Downloads` to the directory where you'd like the files to be written. A directory is automatically created here for whichever SKU you download.

```sh
# Set SKU to one of `ceph_gh`,`ceph_jaws`,`suc_mid1`,`ceph_fh`,`skg_iso`
docker run -it --init --user $UID --volume "$HOME/Downloads:/downloads" --env-file .env ghcr.io/captn3m0/forteller-dl:main SKU /downloads
```

### Running using local PHP

You'll need php, php-curl installed, and replace {SKU} with a valid SKU

```
git clone https://github.com/captn3m0/forteller-dl.git
cd forteller-dl
set -a
source .env
set +a
php run.php SKU [/optional/path/to/output/dir]
```

## TODO

The script is functional enough for me, so this will likely never get done. But ideas:

- [x] Run with Docker
- [ ] Cleanup code
- [ ] API Documentation?
- [ ] Tag the MP3 files as they are saved

## License

Licensed under the [MIT License](https://nemo.mit-license.org/). See LICENSE file for details.
