<?php

namespace Acts\SphinxRealTimeBundle\Doctrine;

use Acts\SphinxRealTimeBundle\Transformer\SphinxToModelTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Maps Sphinx documents with Doctrine objects
 * This mapper assumes an exact match between
 * sphinx documents ids and doctrine object ids
 */
abstract class AbstractSphinxToModelTransformer implements SphinxToModelTransformerInterface
{
    /**
     * Manager registry
     */
    protected $registry = null;

    /**
     * Class of the model to map to the Sphinx documents
     *
     * @var string
     */
    protected $objectClass = null;

    /**
     * Optional parameters
     *
     * @var array
     */
    protected $options = array(
        'hydrate'    => true,
		'identifier' => 'id'
    );

    protected $num_indexes;

    /**
     * Instantiates a new Mapper
     *
     * @param object $registry
     * @param string $objectClass
     * @param array $options
     */
    public function __construct($registry, $objectClass, $num_indexes, array $options = array())
    {
        $this->registry    = $registry;
        $this->objectClass = $objectClass;
        $this->options     = array_merge($this->options, $options);
    }

    /**
     * Returns the object class that is used for conversion.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Transforms an array of sphinx objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array of sphinx objects
     * @return array
     **/
    public function transform(array $sphinxObjects)
    {
        $ids = array();
        foreach ($sphinxObjects as $sphinxObject) {
            $ids[] = floor($sphinxObject['id'] / $this->num_indexes);
        }

        $objects = $this->findByIdentifiers($ids, $this->options['hydrate']);
        if (count($objects) < count($sphinxObjects)) {
            throw new \RuntimeException('Cannot find corresponding Doctrine objects for all Sphinx results.');
        };

        $accessor = PropertyAccess::createPropertyAccessor();
        $identifier_key = $this->options['identifier'];

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        usort($objects, function($a, $b) use ($idPos, $accessor, $identifier_key)
        {
            return $idPos[$accessor->getValue($a, $identifier_key)] > $idPos[$accessor->getValue($b, $identifier_key)];
        });

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return $this->options['identifier'];
    }

    /**
     * Fetches objects by theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected abstract function findByIdentifiers(array $identifierValues, $hydrate);
}
