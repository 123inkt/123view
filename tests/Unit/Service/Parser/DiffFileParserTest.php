use PHPUnit\Framework\Attributes\CoversClass;
#[CoversClass(DiffFileParser::class)]
     * @throws ParseException
     */
    public function testParseFileBinaryData(): void
    {
        // prepare data
        $contents = "old mode 100644\nnew mode 100755\nBinary files /dev/null and b/test-change-file.xml differ\n";

        $result = $this->parser->parse($contents, new DiffFile());
        static::assertTrue($result->binary);
    }

    /**