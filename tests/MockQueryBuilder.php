<?php

namespace WechatMiniProgramSecurityBundle\Tests;

/**
 * 模拟 Doctrine QueryBuilder 的简单实现
 */
class MockQueryBuilder
{
    private mixed $result = null;

    public function __construct(private string $alias = 'a')
    {
    }

    /**
     * 设置要返回的结果
     */
    public function setResult(mixed $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * 模拟 orderBy 方法
     */
    public function orderBy(string $sort, string $order): self
    {
        // 简单返回自身以支持链式调用
        return $this;
    }

    /**
     * 模拟 setMaxResults 方法
     */
    public function setMaxResults(int $maxResults): self
    {
        // 简单返回自身以支持链式调用
        return $this;
    }

    /**
     * 模拟 getQuery 方法
     */
    public function getQuery(): self
    {
        // 简单返回自身以支持链式调用
        return $this;
    }

    /**
     * 模拟 getOneOrNullResult 方法
     */
    public function getOneOrNullResult(): mixed
    {
        return $this->result;
    }
} 