<?php

namespace Mralston\Mug\Enums;

/**
 * The 14 electricity distribution regions, by the distributor id that starts each MPAN core (MUG's
 * `distIds`), with the GSP group each one is.
 */
enum DnoRegion: int
{
    case Eastern = 10;
    case EastMidlands = 11;
    case London = 12;
    case MerseysideAndNorthWales = 13;
    case WestMidlands = 14;
    case NorthEastern = 15;
    case NorthWestern = 16;
    case NorthernScotland = 17;
    case SouthernScotland = 18;
    case SouthEastern = 19;
    case Southern = 20;
    case SouthWales = 21;
    case SouthWestern = 22;
    case Yorkshire = 23;

    /**
     * The GSP group id, as MUG's `gspGroupId` gives it (`_A` to `_P`).
     */
    public function gspGroupId(): string
    {
        return match ($this) {
            self::Eastern => '_A',
            self::EastMidlands => '_B',
            self::London => '_C',
            self::MerseysideAndNorthWales => '_D',
            self::WestMidlands => '_E',
            self::NorthEastern => '_F',
            self::NorthWestern => '_G',
            self::Southern => '_H',
            self::SouthEastern => '_J',
            self::SouthWales => '_K',
            self::SouthWestern => '_L',
            self::Yorkshire => '_M',
            self::SouthernScotland => '_N',
            self::NorthernScotland => '_P',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Eastern => 'Eastern',
            self::EastMidlands => 'East Midlands',
            self::London => 'London',
            self::MerseysideAndNorthWales => 'Merseyside and North Wales',
            self::WestMidlands => 'West Midlands',
            self::NorthEastern => 'North Eastern',
            self::NorthWestern => 'North Western',
            self::NorthernScotland => 'Northern Scotland',
            self::SouthernScotland => 'Southern Scotland',
            self::SouthEastern => 'South Eastern',
            self::Southern => 'Southern',
            self::SouthWales => 'South Wales',
            self::SouthWestern => 'South Western',
            self::Yorkshire => 'Yorkshire',
        };
    }

    /**
     * The region for a GSP group id, with or without its leading underscore.
     */
    public static function tryFromGspGroupId(?string $gspGroupId): ?self
    {
        if ($gspGroupId === null || trim($gspGroupId) === '') {
            return null;
        }

        $normalised = '_'.strtoupper(ltrim(trim($gspGroupId), '_'));

        foreach (self::cases() as $region) {
            if ($region->gspGroupId() === $normalised) {
                return $region;
            }
        }

        return null;
    }
}
