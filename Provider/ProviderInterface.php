<?php

namespace Acts\SphinxRealTimeBundle\Provider;

/**
 * Insert application domain objects into Sphinx index
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ProviderInterface
{
    /**
     * Persists all domain objects to Sphinx for this provider.
     *
     * @param Closure $loggerClosure
     */
    function populate(\Closure $loggerClosure = null);
}
