<?php

namespace App\Services;

class VenueCatalog
{
    /**
     * Curated catalog of world cricket stadiums with capacity, country, city, and HD photos
     */
    public static function getKnownVenues(): array
    {
        return [
            'county ground, hove' => [
                'name' => 'County Ground, Hove',
                'city' => 'Hove',
                'country' => 'England',
                'capacity' => '7,000',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'Home of Sussex County Cricket Club, offering true bounce and sea-breeze swing bowling conditions.',
            ],
            'grace road, leicester' => [
                'name' => 'Grace Road, Leicester',
                'city' => 'Leicester',
                'country' => 'England',
                'capacity' => '6,000',
                'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&auto=format&fit=crop&q=80',
                'description' => 'Home of Leicestershire CCC, renowned for competitive white-ball and red-ball cricket.',
            ],
            'county ground, derby' => [
                'name' => 'County Ground, Derby',
                'city' => 'Derby',
                'country' => 'England',
                'capacity' => '9,500',
                'image_url' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80',
                'description' => 'Historic Derbyshire county ground with modern pavilion and true batting surfaces.',
            ],
            'new road, worcester' => [
                'name' => 'New Road, Worcester',
                'city' => 'Worcester',
                'country' => 'England',
                'capacity' => '5,500',
                'image_url' => 'https://images.unsplash.com/photo-1531415074868-036b1c5c53ec?w=800&auto=format&fit=crop&q=80',
                'description' => 'Iconic picturesque cricket ground along the River Severn with Worcester Cathedral backdrop.',
            ],
            'riverside ground, chester-le-street' => [
                'name' => 'Riverside Ground, Chester-le-Street',
                'city' => 'Chester-le-Street',
                'country' => 'England',
                'capacity' => '17,000',
                'image_url' => 'https://images.unsplash.com/photo-1577223625816-7546f13df25d?w=800&auto=format&fit=crop&q=80',
                'description' => 'Durham cricket headquarters hosting thrilling international tests and ODIs.',
            ],
            'emirates old trafford, manchester' => [
                'name' => 'Emirates Old Trafford, Manchester',
                'city' => 'Manchester',
                'country' => 'England',
                'capacity' => '26,000',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'Historic Test venue with lively pace and spin, home of Lancashire CCC.',
            ],
            'kensington oval, bridgetown, barbados' => [
                'name' => 'Kensington Oval, Bridgetown, Barbados',
                'city' => 'Bridgetown',
                'country' => 'West Indies',
                'capacity' => '28,000',
                'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&auto=format&fit=crop&q=80',
                'description' => 'The Mecca of Caribbean cricket, host of ICC World Cup finals with electric atmosphere.',
            ],
            'providence stadium, guyana' => [
                'name' => 'Providence Stadium, Guyana',
                'city' => 'Georgetown',
                'country' => 'West Indies',
                'capacity' => '15,000',
                'image_url' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80',
                'description' => 'National stadium of Guyana built for the 2007 Cricket World Cup, known for spin-friendly pitches.',
            ],
            'edgbaston, birmingham' => [
                'name' => 'Edgbaston, Birmingham',
                'city' => 'Birmingham',
                'country' => 'England',
                'capacity' => '25,000',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'One of the most vocal and atmospheric cricket grounds in world cricket.',
            ],
            'sophia gardens, cardiff' => [
                'name' => 'Sophia Gardens, Cardiff',
                'city' => 'Cardiff',
                'country' => 'Wales',
                'capacity' => '15,643',
                'image_url' => 'https://images.unsplash.com/photo-1531415074868-036b1c5c53ec?w=800&auto=format&fit=crop&q=80',
                'description' => 'Home of Glamorgan Cricket with state of the art facilities and high-scoring white-ball wickets.',
            ],
            'headingley, leeds' => [
                'name' => 'Headingley, Leeds',
                'city' => 'Leeds',
                'country' => 'England',
                'capacity' => '21,500',
                'image_url' => 'https://images.unsplash.com/photo-1577223625816-7546f13df25d?w=800&auto=format&fit=crop&q=80',
                'description' => 'Iconic cricket venue legendary for dramatic Ashes comebacks and fast bowling conditions.',
            ],
            'trent bridge, nottingham' => [
                'name' => 'Trent Bridge, Nottingham',
                'city' => 'Nottingham',
                'country' => 'England',
                'capacity' => '17,500',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'World-famous venue known for high ODI totals, lush outfield, and great swing bowling.',
            ],
            'narendra modi stadium' => [
                'name' => 'Narendra Modi Stadium',
                'city' => 'Ahmedabad',
                'country' => 'India',
                'capacity' => '132,000',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'The largest cricket stadium in the world, featuring 4 dressing rooms and LED field lighting.',
            ],
            'm. chinnaswamy stadium' => [
                'name' => 'M. Chinnaswamy Stadium',
                'city' => 'Bengaluru',
                'country' => 'India',
                'capacity' => '33,600',
                'image_url' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80',
                'description' => 'High altitude, fast outfield, batting paradise in the heart of Bengaluru.',
            ],
            'wankhede stadium' => [
                'name' => 'Wankhede Stadium',
                'city' => 'Mumbai',
                'country' => 'India',
                'capacity' => '33,108',
                'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&auto=format&fit=crop&q=80',
                'description' => 'Iconic ground next to the Arabian Sea, home of the 2011 ICC World Cup Final triumph.',
            ],
            'eden gardens' => [
                'name' => 'Eden Gardens',
                'city' => 'Kolkata',
                'country' => 'India',
                'capacity' => '68,000',
                'image_url' => 'https://images.unsplash.com/photo-1577223625816-7546f13df25d?w=800&auto=format&fit=crop&q=80',
                'description' => 'The Colosseum of Indian Cricket, renowned for its deafening crowd and historical finishes.',
            ],
            'melbourne cricket ground' => [
                'name' => 'Melbourne Cricket Ground (MCG)',
                'city' => 'Melbourne',
                'country' => 'Australia',
                'capacity' => '100,024',
                'image_url' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                'description' => 'One of the greatest sporting arenas on Earth, home of Boxing Day Tests.',
            ],
            'sydney cricket ground' => [
                'name' => 'Sydney Cricket Ground (SCG)',
                'city' => 'Sydney',
                'country' => 'Australia',
                'capacity' => '48,000',
                'image_url' => 'https://images.unsplash.com/photo-1531415074868-036b1c5c53ec?w=800&auto=format&fit=crop&q=80',
                'description' => 'Classic traditional ground famous for spin bowling and New Year Tests.',
            ],
            'dubai international cricket stadium' => [
                'name' => 'Dubai International Cricket Stadium',
                'city' => 'Dubai',
                'country' => 'UAE',
                'capacity' => '25,000',
                'image_url' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80',
                'description' => 'Ring of Fire floodlit stadium hosting major ICC tournaments and Asia Cups.',
            ],
        ];
    }

    /**
     * Find best match for a stadium name or generate smart fallback data
     */
    public static function resolveVenueDetails(string $rawName): array
    {
        $clean = strtolower(trim($rawName));
        $known = self::getKnownVenues();

        // Exact or key match
        foreach ($known as $key => $data) {
            if ($clean === $key || str_contains($clean, $key) || str_contains($key, $clean)) {
                return $data;
            }
        }

        // Substring / partial match on individual words (e.g. "Hove", "Leicester", "Cardiff", "Derby", "Worcester")
        foreach ($known as $key => $data) {
            $parts = explode(',', $key);
            foreach ($parts as $p) {
                $p = trim($p);
                if (strlen($p) >= 4 && str_contains($clean, $p)) {
                    return $data;
                }
            }
        }

        // Parse City and Country from comma-separated name (e.g. "Holkar Stadium, Indore")
        $parts = array_map('trim', explode(',', $rawName));
        $cityName = count($parts) > 1 ? $parts[1] : (count($parts) > 0 ? $parts[0] : 'City');
        $countryName = count($parts) > 2 ? $parts[2] : 'International';

        // High quality fallback stadium images pool
        $fallbackImages = [
            'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1531415074868-036b1c5c53ec?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1577223625816-7546f13df25d?w=800&auto=format&fit=crop&q=80',
        ];
        $img = $fallbackImages[abs(crc32($rawName)) % count($fallbackImages)];

        return [
            'name' => $rawName,
            'city' => $cityName,
            'country' => $countryName,
            'capacity' => '20,000',
            'image_url' => $img,
            'description' => "Premier cricket venue located in {$cityName} with top-class sporting pitch.",
        ];
    }
}
