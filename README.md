Remote Image
=================
Quickly get remote images off of other websites. 

## Maintainer Contact
* Buckles

## Requirements
* SilverStripe CMS 3.1.x 


## Installation
* Run composer require buckleshusky/remoteimage dev-master in the project folder
* Run dev/build?flush=all to regenerate the manifest


## Example Usage
```php
//get the remote image for video
$videoImage = new RemoteImage("Title of image", "http://someWebsite.com/image.png");
$videoImage->setFolderName("folderName");
$videoImage->getImage();

//create the link
$this->VideoImageID = $videoImage->makeImageAndLink();
```


#### Configuration Options
You will need to add your google api key to your config.yml
"https://developers.google.com/youtube/v3/getting-started"

```yml
YoutubeData:
    Api:  #Api key required
```