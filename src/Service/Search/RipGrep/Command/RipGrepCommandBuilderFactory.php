<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep\Command;

class RipGrepCommandBuilderFactory
{
    public function default(): RipGrepCommandBuilder
    {
        return (new RipGrepCommandBuilder())
            ->hidden()
            ->noColor()
            ->lineNumber()
            ->beforeContext(5)
            ->afterContext(5)
            ->glob('!.git/')
            ->json();
    }
}
