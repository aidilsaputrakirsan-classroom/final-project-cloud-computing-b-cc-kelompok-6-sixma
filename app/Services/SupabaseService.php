<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
  protected string $baseUrl;
  protected string $anonKey;
  protected ?string $jwt = null;      // << NEW: JWT User jika ada

  protected array $queryParams = [];
  protected string $table = '';

  public function __construct()
  {
    $this->baseUrl = rtrim(env('SUPABASE_REST_URL'), '/');
    $this->anonKey = env('SUPABASE_ANON_KEY');
  }

  /* ============================================================
     | AUTH Override (PENTING untuk RLS)
     ============================================================ */
  public function auth(string $jwt): static
  {
    $this->jwt = $jwt;
    return $this;
  }

  /* ============================================================
     | Table Builder
     ============================================================ */
  public function table(string $table): static
  {
    $this->table = $table;
    $this->queryParams = [];
    return $this;
  }

  public function select(string $columns): static
  {
    $this->queryParams['select'] = $columns;
    return $this;
  }

  public function eq(string $column, $value): static
  {
    $this->queryParams[$column] = "eq.$value";
    return $this;
  }

  public function filterRaw(string $column, string $expression): static
  {
    $this->queryParams[$column] = $expression;
    return $this;
  }

  public function in(string $column, array $values): static
  {
    $list = implode(',', $values);
    $this->queryParams[$column] = "in.($list)";
    return $this;
  }

  public function order(string $column, string $direction = 'asc'): static
  {
    $this->queryParams['order'] = "$column.$direction";
    return $this;
  }

  /* ============================================================
     | Build URL
     ============================================================ */
  private function buildUrl()
  {
    return $this->baseUrl . '/' . $this->table . '?' . http_build_query($this->queryParams);
  }

  /* ============================================================
     | HTTP Client
     ============================================================ */
  private function client()
  {
    $token = $this->jwt ?? $this->anonKey;

    return Http::withHeaders([
      'apikey'        => $this->anonKey,
      'Authorization' => "Bearer {$token}",
      'Content-Type'  => 'application/json',
    ])
      ->timeout(10)
      ->retry(2, 200)
      ->withoutVerifying();
  }

  /* ============================================================
     | GET
     ============================================================ */
  public function get()
  {
    try {
      $response = $this->client()->get($this->buildUrl());
      return $response->json();
    } catch (\Throwable $e) {
      Log::error("Supabase SELECT exception: " . $e->getMessage());
      return null;
    }
  }

  /* ============================================================
     | FIRST
     ============================================================ */
  public function first()
  {
    $result = $this->get();
    return $result[0] ?? null;
  }

  /* ============================================================
     | INSERT (RLS fix → JWT user)
     ============================================================ */
  public function insert(array $data)
  {
    try {
      $response = $this->client()->post("{$this->baseUrl}/{$this->table}", $data);
      return $response->json();
    } catch (\Throwable $e) {
      Log::error("Supabase INSERT error: " . $e->getMessage());
      return null;
    }
  }

  /* ============================================================
     | UPDATE
     ============================================================ */
  public function update(string $id, array $data)
  {
    try {
      $url = "{$this->baseUrl}/{$this->table}?id=eq.$id";
      $response = $this->client()->patch($url, $data);
      return $response->json();
    } catch (\Throwable $e) {
      Log::error("Supabase UPDATE error: " . $e->getMessage());
      return null;
    }
  }

  /* ============================================================
     | DELETE
     ============================================================ */
  public function delete(string $id)
  {
    try {
      $url = "{$this->baseUrl}/{$this->table}?id=eq.$id";
      return $this->client()->delete($url)->json();
    } catch (\Throwable $e) {
      Log::error("Supabase DELETE error: " . $e->getMessage());
      return null;
    }
  }

  /* ============================================================
     | STORAGE UPLOAD
     ============================================================ */
  public function uploadFile(string $bucket, string $filename, string $content, string $mime, string $jwt)
  {
    $url = env('SUPABASE_URL') . "/storage/v1/object/$bucket/$filename";

    return Http::withHeaders([
      'Authorization' => "Bearer $jwt",
      'Content-Type'  => $mime,
      'x-upsert'      => 'true',
    ])
      ->timeout(10)
      ->retry(2, 200)
      ->withBody($content, $mime)
      ->withoutVerifying()
      ->put($url);
  }
}
