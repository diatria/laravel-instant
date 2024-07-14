<?php
namespace Diatria\LaravelInstant\Utils;

class ThrowError
{
    protected $haystack;
    protected $query;
    protected $throw;
    protected $strict;

    public function __construct(
        $haystack,
        $query,
        $throw = null,
        $strict = false
    ) {
        $this->haystack = collect($haystack)->toArray();
        $this->query = $query;
        $this->throw = $throw;
        $this->strict = $strict;
    }

    public function result()
    {
        $query = explode(".", $this->query);

        try {
            $temp = $this->haystack;
            foreach ($query as $item) {
                $temp = (array) $temp[$item];
            }
        } catch (\Exception $e) {
            return $this->throw;
        }

        if (count($temp) == 1) {
            return $this->strict ? $temp : array_values($temp)[0];
        }

        // jika array
        if (count($temp) > 1) {
            return $temp;
        }

        // jika kosong dan ada callback
        if (isset($this->throw)) {
            return $this->throw;
        }

        return null;
    }
}
