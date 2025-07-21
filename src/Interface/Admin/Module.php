<?php

namespace Echo\Interface\Admin;

interface Module
{
    public function index(): array;
    public function create(): array;
    public function show(int $id): array;
    public function edit(int $id): array;
    public function store();
    public function update(int $id);
    public function destroy(int $id);
}
