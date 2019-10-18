# openwebif-xmltv-converter

This small application converts the JSON guide data from OpenWebIf to XMLTV. It supports reading the guide data 
directly from a URL, and outputs the XMLTV on stdout. The main usage is to get guide data from Enigma2 into tvheadend 
for channels that don't have proper EIT data embedded.

Currently genre/category information is lost - pull requests are very welcome if this is important to you!

## Requirements

* PHP >= 7.0 with the `xml` and `curl` extensions enabled

On Debian 9, these can be installed by running:

```bash
sudo apt-get install php-cli php-xml php-curl
```

## Usage

This example illustrates how you can basically copy the guide data from an Enigma2 set-top box into a tvheadend 
instance.

Assumptions made in these examples:

* The commands are run as the `hts` user
* The Enigma2 set-top box has the IP address 192.168.1.201
* You have enabled external XMLTV support in tvheadend (xmltv.sock)
* You have `socat` installed

1. Clone the repository:

```
git clone https://github.com/Jalle19/openwebif-xmltv-converter.git
```

2. Run the following command:

```
php openwebif-xmltv-converter/openwebif-xmltv-converter.php "http://192.168.1.201/api/epgmultigz?bRef=1:7:1:0:0:0:0:0:0:0:FROM%20BOUQUET%20%22userbouquet.abm.sat_282_freesat.main.tv%22%20ORDER%20BY%20bouquet" | socat - UNIX-CONNECT:/home/hts/.hts/tvheadend/epggrab/xmltv.sock
```

This is just an example, you will most likely need to figure out the correct URL to use yourself.

3. You should now see something like this in tvheadend if you have debug logging enabled:

```
2019-10-18 12:45:24.551 xmltv: xmltv: grab took 18 seconds
2019-10-18 12:45:26.131 xmltv: xmltv: parse took 1 seconds
2019-10-18 12:45:26.131 xmltv: xmltv:  channels   tot=  117 new=    0 mod=    0
2019-10-18 12:45:26.131 xmltv: xmltv:  brands     tot=    0 new=    0 mod=    0
2019-10-18 12:45:26.131 xmltv: xmltv:  seasons    tot=    0 new=    0 mod=    0
2019-10-18 12:45:26.131 xmltv: xmltv:  episodes   tot=    0 new=    0 mod=    0
2019-10-18 12:45:26.131 xmltv: xmltv:  broadcasts tot=26042 new=    0 mod=23417 
```

## License

MIT
