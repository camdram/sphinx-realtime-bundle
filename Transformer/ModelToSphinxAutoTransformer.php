<?php
namespace Acts\SphinxRealTimeBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Maps Sphinx documents with Doctrine objects
 * This mapper assumes an exact match between
 * Sphinx documents ids and doctrine object ids
 */
class ModelToSphinxAutoTransformer implements ModelToSphinxTransformerInterface
{
    /**
     * Optional parameters
     *
     * @var array
     */
    protected $options = array(
        'identifier' => 'id'
    );

    /**
     * Instanciates a new Mapper
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Transforms an object into an sphinx object having the required keys
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return array
     **/
    public function transform($object, array $fields)
    {
        $accessor = PropertyAccess::getPropertyAccessor();
        $identifier = $accessor->getValue($object, $this->options['identifier']);
        $document           = array('id' => $identifier);

        foreach ($fields as $key) {
            $property = $accessor->getValue($object, $key);
            $document[$key] = $this->normalizeValue($property);

        }
        return $document;
    }

    /**
     * Attempts to convert any type to a string or an array of strings
     *
     * @param mixed $value
     *
     * @return string|array
     */
    protected function normalizeValue($value)
    {
        $normalizeValue = function(&$v)
        {
            if ($v instanceof \DateTime) {
                $v = $v->format('c');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string)$v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }

}
