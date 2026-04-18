<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Entity\Repository\Repository;

readonly class CodeOwnerFinder
{
    public function __construct(
        private CodeOwnerFileFinder $fileFinder,
        private CodeOwnerFileParser $parser,
        private CodeOwnerFilepathMatcher $matcher,
    ) {
    }

    /**
     * @return list<string>
     */
    public function find(Repository $repository, string $filepath): array
    {
        $files = $this->fileFinder->find($repository, $filepath);

        foreach ($files as $file) {
            $patterns = array_reverse($this->parser->parse((string)file_get_contents($file)));
            $match    = $this->matcher->match($filepath, $patterns);
            if ($match !== null) {
                return $match->owners;
            }
        }

        return [];
    }
}
