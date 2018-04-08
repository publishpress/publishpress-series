<?php
namespace OrganizeSeries\domain\model;

use InvalidArgumentException;

/**
 * FileLocation
 * This is a value object for a file location.
 * Automatically validates that the given file path resolves to a readable file.
 *
 * @package OrgnaizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class FileLocation
{
    /**
     * The path to the file
     * @var string
     */
    private $file_path = '';

    /**
     * FileLocation constructor.
     *
     * @param string $file_path
     * @throws InvalidArgumentException
     */
    public function __construct($file_path) {
        $this->setFilePath($file_path);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @param string $file_path
     * @throws InvalidArgumentException
     */
    private function setFilePath($file_path)
    {
        if (! is_readable($file_path)) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__('The given file path (%s) is not readable.', 'organize-series'),
                    $file_path
                )
            );
        }
        $this->file_path = $file_path;
    }
}