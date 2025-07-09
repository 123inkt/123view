export function ltrim(value: string, char: string): string {
  while (value.length > 0 && value[0] === char) {
    value = value.substring(1);
  }
  return value;
}

/**
 * Checks if a string contains all the words in the search string.
 */
export function contains(value: string, search: string | string[]): boolean {
  value  = value.toLowerCase();
  search = Array.isArray(search) ? search : search.split(' ');

  return search.every(s => value.includes(s.toLowerCase()));
}
