export function basename(path: string) {
    const index = path.lastIndexOf('/');

    return index === -1 ? path : path.substring(index + 1);
}
