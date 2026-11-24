<?php

namespace Mralston\Mug\Exceptions;

use RuntimeException;

/**
 * MUG answered, but not with what was asked for: a refusal in the body of a successful response, or
 * a body that is not the JSON its API description promises.
 */
class MugException extends RuntimeException {}
