export function ltrim(value: string, char: string): string {
    while (value.length > 0 && value[0] === char) {
      value = value.substring(1);
    }
    return value;
  }
