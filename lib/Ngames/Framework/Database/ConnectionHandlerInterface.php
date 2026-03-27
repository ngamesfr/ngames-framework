<?php

/*
 * Copyright (c) 2014-2021 NGames
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ngames\Framework\Database;

/**
 * Interface for database connection handlers.
 * Allows replacing the real PDO-based connection with a mock for testing.
 */
interface ConnectionHandlerInterface
{
    /**
     * @return array|false
     */
    public function query(string $query, array $params = []): array|false;

    /**
     * @return array|false
     */
    public function queryOne(string $query, array $params = []): array|false;

    /**
     * @return int|false
     */
    public function exec(string $query, array $params = []): int|false;

    /**
     * @return int|false
     */
    public function insert(string $tableName, array $data): int|false;

    /**
     * @return int|false
     */
    public function count(string $query, array $params = []): int|false;

    /**
     * @return array|false
     */
    public function findOneById(string $tableName, int|string $id): array|false;

    /**
     * Returns a PDO-compatible object for edge cases like quote().
     * May return null when not supported (e.g. in mock handlers).
     */
    public function getConnection(): ?\PDO;
}
