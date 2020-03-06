<?php
/**
 * This file is part of the zeni18.
 *
 * (c) zeni18 <hi.zero.im@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace zeni18\container\build;

interface ServiceProviderInterface
{
    public function register(Container $container);
}
