<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Shared\Process\Stream;

interface WriteStreamInterface extends StreamInterface
{
    /**
     * @param array $data
     *
     * @return int
     */
    public function write(array $data): int;

    /**
     * @return bool
     */
    public function flush(): bool;
}