<?php namespace Foil\Context;

use Foil\Contracts\ContextCollectionInterface;
use Foil\API;
use Foil\Contracts\APIAwareInterface;
use Foil\Contracts\ContextInterface;
use Foil\Traits;
use SplObjectStorage;

/**
 * Collect other context classes and manage them.
 *
 * @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Collection implements ContextCollectionInterface, APIAwareInterface
{
    use Traits\APIAwareTrait,
        Traits\DataHandlerTrait;

    private $storage;
    private $template;
    private $allowed;

    public function __construct(API $api)
    {
        $this->storage = new SplObjectStorage();
        $this->setAPI($api);
        $this->allow();
    }

    public function accept($template)
    {
        if ($this->allowed() && is_string($template)) {
            $this->template = $template;

            return true;
        }

        return false;
    }

    public function add(ContextInterface $context)
    {
        $this->storage->attach($context);
    }

    /**
     * Provide data by collecting it from contexts in the collection that accepts current template.
     * After having collected data from a context, an event is fired allowing to add some data
     * that depends on that specific context data.
     * {@inheritdoc}
     */
    public function provide()
    {
        $data = $this->data();
        $template = $this->template();
        if (empty($template)) {
            return $data;
        }
        $storage = $this->storage();
        $storage->rewind();
        while ($storage->valid()) {
            $context = $storage->current();
            if ($context->accept($template)) {
                $data = array_merge($data, $context->provide());
                $this->api()->fire('f.context.provided', $context, $this);
            }
            $storage->next();
        }
        $this->api()->fire('f.context.allprovided', $this);

        return $data;
    }

    public function has(ContextInterface $context)
    {
        $this->storage->contains($context);
    }

    public function remove(ContextInterface $context)
    {
        $this->storage->detach($context);
    }

    /**
     * @return SplObjectStorage
     */
    public function storage()
    {
        return $this->storage;
    }

    /**
     * @return string|void
     */
    public function template()
    {
        return $this->template;
    }

    /**
     * @return boolean
     */
    public function allowed()
    {
        return $this->allowed;
    }

    public function allow()
    {
        $this->allowed = true;
    }

    public function disallow()
    {
        $this->allowed = false;
    }
}
