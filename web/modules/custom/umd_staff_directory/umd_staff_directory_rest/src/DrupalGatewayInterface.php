<?php

namespace Drupal\umd_staff_directory_rest;

/**
 * Interface for Staff Directory interactions with the Drupal database.
 */
interface DrupalGatewayInterface {

  /**
   * Adds a new UmdTerpPerson entry.
   *
   * UmdTerpPerson is populated with the staff directory values in the
   * given array.
   *
   * @param array $staff_dir_values
   *   The staff directory values to populate the entry.
   */
  public function addEntry(array $staff_dir_values);

  /**
   * Updates the UmdTerpPerson entry with the given directory id.
   *
   * UmdTerpPerson is populated with the staff directory values in the array.
   *
   * @param string $directory_id
   *   The UMD directory id for the UmdPerson entry to update.
   * @param array $staff_dir_values
   *   The staff directory values to populate the entry.
   */
  public function updateEntry(string $directory_id, array $staff_dir_values);

  /**
   * Removes (by unpublishing) the UmdTerpPerson with the given directory id.
   *
   * @param string $directory_id
   *   The UMD directory id of the UmdTerpPerson to remove (unpublish).
   */
  public function removeEntry(string $directory_id);

  /**
   * Republishes the UmdTerpPerson with the given directory id.
   *
   * @param string $directory_id
   *   The UMD directory id of the UmdTerpPerson to republish.
   */
  public function republishEntry(string $directory_id);

  /**
   * Returns an array of directory ids representing unpublished UmdTerpPersons.
   *
   * @return array
   *   An array of directory ids representing unpublished UmdTerpPersons.
   */
  public function getUnpublishedUmdTerpPersonDirectoryIds();

  /**
   * Returns an associative array of arrays representing UmdTermPersons.
   *
   * Returned array contains all UmdTerpPersons (published and unpublished)
   * in the system, indexed by UMD directory id.
   *
   * Each value array is an associative array containing key/value pairs
   * consistent with the data coming from the incoming JSON file.
   *
   * @return array
   *   An associative array of arrays representing UmdTermPersons.
   */
  public function umdTerpPersonsToStaffDirectoryArray();

}
