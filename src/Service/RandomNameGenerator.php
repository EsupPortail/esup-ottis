<?php

namespace App\Service;

/**
 * RandomNameGenerator - Service for generating random user names
 *
 * This service generates random, fun names for anonymous users in the format:
 * "Color Animal" (e.g., "Red Dragon", "Blue Tiger")
 */
class RandomNameGenerator
{
    /**
     * @var array List of colors to use for name generation
     */
    private array $colors = ['Red', 'Blue', 'Orange', 'Purple', 'Black', 'White', 'Green'];

    /**
     * @var array List of objects/animals to use for name generation
     */
    private array $objects = ['Alpaca', 'Alligator', 'Dragoon', 'Elephant', 'Lion', 'Panther', 'Tiger', 'Zebra'];

    /**
     * Constructor
     *
     * @param array $colors Custom list of colors (optional)
     * @param array $objects Custom list of objects/animals (optional)
     */
    public function __construct(array $colors = [], array $objects = [])
    {
        if (!empty($colors)) {
            $this->colors = $colors;
        }
        if (!empty($objects)) {
            $this->objects = $objects;
        }
    }

    /**
     * Generate a random name
     *
     * @return string Random name in format "Color Object"
     */
    public function generate(): string
    {
        $color = $this->colors[array_rand($this->colors)];
        $object = $this->objects[array_rand($this->objects)];
        return $color . ' ' . $object;
    }

    /**
     * Generate multiple random names
     *
     * @param int $count Number of names to generate
     * @return array Array of random names
     */
    public function generateMultiple(int $count): array
    {
        $names = [];
        for ($i = 0; $i < $count; $i++) {
            $names[] = $this->generate();
        }
        return $names;
    }

    /**
     * Static helper method to generate a random name with default configuration
     *
     * @return string Random name
     */
    public static function generateName(): string
    {
        $generator = new self();
        return $generator->generate();
    }

    /**
     * Get the list of colors
     *
     * @return array List of colors
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * Get the list of objects
     *
     * @return array List of objects
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
