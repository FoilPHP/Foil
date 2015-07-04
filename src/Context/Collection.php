<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Context;

use Foil\Contracts\ContextCollectionInterface;
use Foil\Contracts\ContextInterface;
use Foil\Kernel\Events;
use Foil\Traits;
use SplObjectStorage;

/**
 * Collect other context classes and manage them.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Collection implements ContextCollectionInterface
{
    use Traits\DataHandlerTrait;

    /**
     * @var \Foil\Kernel\Events
     */
    private $events;

    /**
     * @var \SplObjectStorage
     */
    private $storage;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $allowed;

    /**
     * @param \Foil\Kernel\Events $events
     */
    public function __construct(Events $events)
    {
        $this->events = $events;
        $this->storage = new SplObjectStorage();
        $this->allow();
    }

    /**
     * @inheritdoc
     */
    public function accept($template)
    {
        if ($this->allowed() && is_string($template)) {
            $this->template = $template;

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
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
                $this->events->fire('f.context.provided', $context, $this);
            }
            $storage->next();
        }
        $this->events->fire('f.context.allprovided', $this);

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function has(ContextInterface $context)
    {
        return $this->storage->contains($context);
    }

    /**
     * @inheritdoc
     */
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
