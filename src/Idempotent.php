<?php

namespace Yeknava\LaravelIdempotent;

use Closure;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Yeknava\LaravelIdempotent\Exceptions\DuplicatedIdempotencyKeyException;
use Yeknava\LaravelIdempotent\Exceptions\IdempotencyKeyCharacterLimitException;
use Yeknava\LaravelIdempotent\Exceptions\MissingIdempotencyKeyException;

class Idempotent
{
    const PREFIX = 'IDEMPOTENT_';

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The application cache repository.
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    public function __construct(Application $app, CacheRepository $cache)
    {
        $this->app = $app;
        $this->cache = $cache;
    }

    /**
     * Determine if the HTTP request method is HEAD or OPTIONS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkip($request)
    {
        return in_array(
            $request->method(),
            config('idempotent.skip_http_methods', [
                'HEAD',
                'OPTIONS'
            ])
        );
    }

    /**
     * Get checksum of request payload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function checksumPayload($request)
    {
        return md5(json_encode($request->all()));
    }

    public function handle(Request $request, Closure $next, $force = null)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if (is_null($force)) {
            $force = config('idempotent.default', true);
        }

        $idempotencyKey =$request->input('_idempotency_key') ?:
            $request->header(config('idempotent.header_key', 'Idempotency-Key'));
        $force = filter_var($force, FILTER_VALIDATE_BOOLEAN);

        // make it optional if request method is get
        if ($request->method() === 'GET') {
            $force = false;
        }

        if (! $idempotencyKey && $force === true) {
            throw new MissingIdempotencyKeyException();
        }

        if (strlen($idempotencyKey) > 38) {
            throw new IdempotencyKeyCharacterLimitException();
        }

        $cacheKey = self::PREFIX.$idempotencyKey.$request->fingerprint();

        $cachedResponse = $this->cache->get($cacheKey);
        $payloadChecksum = $this->checksumPayload($request);

        if ($cachedResponse) {
            $decodedValue = json_decode($cachedResponse);

            if ($decodedValue->payload_checksum !== $payloadChecksum) {
                throw new DuplicatedIdempotencyKeyException();
            }

            return unserialize($decodedValue->response);
        }

        $response = $next($request);
        $encodedData = json_encode([
            'payload_checksum' => $payloadChecksum,
            'response' => serialize($response),
        ]);

        $this->cache->set(
            $cacheKey,
            $encodedData,
            config('idempotent.expires_in', 86400)
        );

        return $response;
    }
}
