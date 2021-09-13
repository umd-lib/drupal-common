<?php

namespace Drupal\umd_staff_directory_rest;
use Drupal\node\Entity\Node;

interface DrupalGateway
{
    public function addEntry(array $staff_dir_values);
    public function updateEntry(string $directory_id, array $staff_dir_values);
    public function removeEntry(string $directory_id);
    public function republishEntry(string $directory_id);
    public static function getUnpublishedUmdTerpPersonDirectoryIds();
    public function umdTerpPersonsToJsonArray();
}
