Display of geotagged images
===========================

Modern phones allow you to geotag your images and social networks have been showing this data for some time now.

Only problem - there was no easy way of showing a selection of your images on your own map. Now there is.

The app takes in images, parses the geotag data, creates thumbnails and show them as pins with popups on a google map.

Example:
http://map.andree.si/

Deployment
----------

1. Clone repository

2. Copy nginx.conf

    ```
    cp nginx.conf.dist nginx.conf
    ```

3. Edit `nginx.conf` file to suit your needs (paths and server_name)

4. Add images to `original_img` folder

5. Run process_imgs.php file - this will generate the needed files

  ```
  php process_imgs.php
  ```
  
6. Open in browser (I know, the hard part)

You can use the page as is or embed it into a nother page. If you wish you can change the HTML in `web/index.html` .

Debuging
--------

Test the images with

    php exif_test.php path/to/image.jpg

if you are having problems with geotaged images


Tested with
-----------

JPEG
- generated by iPhone
- generated by HTC Android 2.3.5
