<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Observer;

interface SubscriberInterface
{
    /**
     * @param State $state
     * @return mixed
     */
    public function update(State $state);
}