#!/bin/bash

###############
# definitions #
###############

#          0      0      2      3      4      4      6      8
LOCALES=( 'enUS' 'enGB' 'frFR' 'deDE' 'zhCN' 'enCN' 'esES' 'ruRU')

LOCALIZED=(
    "dbfilesclient\\*.dbc"
    "interface\\worldmap\\*.blp"
    "interface\\framexml\\globalstrings.lua"
    "sound\\*"
)

NEUTRAL=(
    "sound\\*"
    "interface\\talentframe\\*.blp"
    "interface\\icons\\*.blp"
    "interface\\spellbook\\*.blp"
    "interface\\paperdoll\\*.blp"
    "interface\\pictures\\*.blp"
    "interface\\PvPRankBadges\\*.blp"
    "interface\\FlavorImages\\*.blp"
    "interface\\glues\\charactercreate\\*.blp"
    "interface\\calendar\\holidays\\*.blp"
    "interface\\pvpframe\\*.blp"
)

GOODIES=(
    "interface\\glues\\loadingscreens\\*.blp"
    "interface\\glues\\credits\\*.blp"
)

LOC_FOUND=0
DONE_ONCE=0

IN_PATH=''
OUT_PATH=''
DO_EXTRA=''
EXT_PATH=''


###################
# check variables #
###################


if [[ -x MPQExtractor ]]; then
    EXT_PATH="./MPQExtractor"
else
    while [ ! -x "$EXT_PATH" ]; do
        echo -e "could not find the \033[01;37mMPQExtractor\033[0m executable at './$EXT_PATH'. Where is it?"
        read x
        EXT_PATH=$x
    done
fi

if [ -n "$1" ]; then
    IN_PATH=$1
fi

if [ -n "$2" ]; then
    OUT_PATH=$2
fi

if [ "$3" == "true" ] || [ "$3" == "1" ] || [ "$3" == "yes" ] || [ "$3" == "y" ]; then
    DO_EXTRA=1
elif [ -z "$3" ]; then
    echo "do you also want to extract the optional goodies? (loadingscreens & credits):"
    read x

    if [ "$x" == "true" ] || [ "$x" == "1" ] || [ "$x" == "yes" ] || [ "$3" == "y" ]; then
        DO_EXTRA=1
    else
        DO_EXTRA=0
    fi
else
    DO_EXTRA=0
fi

if [ -z "$IN_PATH" ]; then
    echo "please enter the path to the mpq archives (e.g. '../data'):"
    read IN_PATH
fi

if [ -z "$OUT_PATH" ]; then
    echo "please enter the path to the setup data directory (e.g. '../mpqdata'):"
    read OUT_PATH
fi


#############
# execution #
#############


for i in "${LOCALES[@]}"; do

    if [ $LOC_FOUND -eq 0 ] && [ -z $i ]; then
        echo "could not determine the locale. Please provide one: 'enUS' 'enGB' 'frFR' 'deDE' 'zhCN' 'enCN' 'esES' 'ruRU'"
        read curLoc
    elif [ ! -d $IN_PATH/$i ]; then
        continue
    else
        LOC_FOUND=1
        curLoc=$i
    fi

    if [ -z "$curLoc" ]; then
        break
    fi

    sharedMPQs=(
        "../common.MPQ"
        "../expansion.MPQ"
        "../lichking.MPQ"
        "../patch.MPQ"
        "../patch-2.MPQ"
        "../patch-3.MPQ"
    )

    localeMPQs=(
        "locale-$curLoc.MPQ"
        "speech-$curLoc.MPQ"
        "expansion-locale-$curLoc.MPQ"
        "expansion-speech-$curLoc.MPQ"
        "lichking-locale-$curLoc.MPQ"
        "lichking-speech-$curLoc.MPQ"
        "patch-$curLoc.MPQ"
        "patch-$curLoc-2.MPQ"
        "patch-$curLoc-3.MPQ"
    )

    inPath=$IN_PATH/$curLoc/
    outPath=$OUT_PATH/$curLoc/
    if [ $LOC_FOUND -eq 0 ]; then
        inPath=$IN_PATH/
    fi

    if [ ! -d $outPath ]; then
        if ! mkdir -p "$outPath" ; then
            echo -e "[\033[0;31mERR\033[0m] could not create output folder \033[01;37m$outPath\033[0m"
            exit
        fi
    fi

    echo ""
    echo -e "extracting archives from \033[01;37m$inPath\033[0m into \033[01;37m$outPath\033[0m"
    echo ""

    sleep 1;


    if [ $DONE_ONCE -ne 1 ]; then
        for j in "${sharedMPQs[@]}"; do
            usePath=$inPath$j

            if [ -r $usePath ]; then
                for pattern in "${LOCALIZED[@]}"; do
                    echo "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
                    "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
                done

                echo -e "[\033[0;32mOK\033[0m]  successfully extracted \033[01;37m$usePath\033[0m"
            else
                echo -e "[\033[0;31mERR\033[0m] could not read archive \033[01;37m$usePath\033[0m"
                exit
            fi
        done
    fi


    for j in "${localeMPQs[@]}"; do
        usePath=$inPath$j

        if [ -r $usePath ]; then
            for pattern in "${LOCALIZED[@]}"; do
                echo "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
                "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
            done

            if [ $DONE_ONCE -eq 1 ]; then
                echo -e "[\033[0;32mOK\033[0m]  successfully extracted \033[01;37m$usePath\033[0m"
                continue
            fi

            for pattern in "${NEUTRAL[@]}"; do
                echo "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
                "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
            done

            if [ $DO_EXTRA -eq 1 ]; then
                for pattern in "${GOODIES[@]}"; do
                    "$EXT_PATH" -e "$pattern" -f --excludeflags 0x02000000 -o "$outPath" "$usePath"
                done
            fi

            echo -e "[\033[0;32mOK\033[0m]  successfully extracted \033[01;37m$usePath\033[0m"
        else
            echo -e "[\033[0;31mERR\033[0m] could not read archive \033[01;37m$usePath\033[0m"
            exit
        fi
    done

    DONE_ONCE=1
done
