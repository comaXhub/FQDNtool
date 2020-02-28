# Usage: used-tld.sh name
#
# list all used TLD for a given SLD

# get the list of the domains

[ -f ./tlds.txt ] ||
wget -O- http://data.iana.org/TLD/tlds-alpha-by-domain.txt 2> /dev/null |
sed 1d > ./tlds.txt


#	#IANA does not have SLDs so we add them manually:
#	echo -e "AE.ORG\nAR.COM\nBR.COM\nCN.COM\nCO.COM\nCO.UK\nCOM.DE\nDE.COM\nEU.COM\nGB.COM\nGB.NET\nGOV.UK\nGR.COM\nHU.COM\nHU.NET\nIN.NET\nJP.NET\nJPN.COM\nKR.COM\nME.UK\nNO.COM\nORG.UK\nQC.COM\nRADIO.AM\nRADIO.FM\nRU.COM\nSA.COM\nSE.COM\nSE.NET\nUK.COM\nUK.NET\nUS.COM\nUS.ORG\nUY.COM\nZA.COM" >> /tmp/tlds.txt


# remove the false positive by checking a long random domain name
# though, we cannot check for these domains now

if [ ! -f ./tlds-false-positive.txt ]
then
	touch ./tlds-false-positive.txt
	$0 "$(
		tr -cd 0-9a-z < /dev/urandom | dd bs=32 count=1 2> /dev/null
	)" | sed 's/.*\.//' > ./tlds-false-positive.txt
fi


# check every domain, up to 5 at once

i=0
while read tld
do
    i=$((i + 1))
    dig any "$1.$tld" | grep -q 'ANSWER: 0' || echo "$1.$tld" &
    [ "$i" = 5 ] && wait
done << EOF
$(grep -vFf ./tlds-false-positive.txt ./tlds.txt)
EOF

wait
