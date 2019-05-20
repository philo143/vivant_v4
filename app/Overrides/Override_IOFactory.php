<?php 
namespace App\Overrides;

class Override_IOFactory extends \PHPExcel_IOFactory
{
	/**
     * Search locations
     *
     * @var    array
     * @access    private
     * @static
     */
    private static $searchLocations = array(
        array( 'type' => 'IWriter', 'path' => 'PHPExcel/Writer/{0}.php', 'class' => 'PHPExcel_Writer_{0}' ),
        array( 'type' => 'IReader', 'path' => 'PHPExcel/Reader/{0}.php', 'class' => 'PHPExcel_Reader_{0}' )
    );

    /**
     * Autoresolve classes
     *
     * @var    array
     * @access    private
     * @static
     */
    private static $autoResolveClasses = array(
        'Excel2007',
        'Excel5',
        'Excel2003XML',
        'OOCalc',
        'SYLK',
        'Gnumeric',
        'HTML',
        'CSV',
    );

    /**
     *    Private constructor for PHPExcel_IOFactory
     */
    private function __construct()
    {
    }

    /**
     * Get search locations
     *
     * @static
     * @access    public
     * @return    array
     */
    public static function getSearchLocations()
    {
        return self::$searchLocations;
    }

    /**
     * Set search locations
     *
     * @static
     * @access    public
     * @param    array $value
     * @throws    PHPExcel_Reader_Exception
     */
    public static function setSearchLocations($value)
    {
        if (is_array($value)) {
            self::$searchLocations = $value;
        } else {
            throw new PHPExcel_Reader_Exception('Invalid parameter passed.');
        }
    }

    /**
     * Add search location
     *
     * @static
     * @access    public
     * @param    string $type        Example: IWriter
     * @param    string $location    Example: PHPExcel/Writer/{0}.php
     * @param    string $classname     Example: PHPExcel_Writer_{0}
     */
    public static function addSearchLocation($type = '', $location = '', $classname = '')
    {
        self::$searchLocations[] = array( 'type' => $type, 'path' => $location, 'class' => $classname );
    }

    public static function createReader($readerType = '')
    {
        // Search type
        $searchType = 'IReader';

        // Include class
        foreach (self::$searchLocations as $searchLocation) {
            if ($searchLocation['type'] == $searchType) {
            	if($readerType == "Excel2003XML"){
            		$className = "App\Overrides\Override_Reader_Excel2003XML";
            	}else{
            		$className = str_replace('{0}', $readerType, $searchLocation['class']);
            	}
                

                $instance = new $className();
                if ($instance !== null) {
                    return $instance;
                }
            }
        }

        // Nothing found...
        throw new PHPExcel_Reader_Exception("No $searchType found for type $readerType");
    }

}