<?php

namespace PhpMx\Input;

use Closure;
use Exception;
use PhpMx\Prepare;

/** Classe para definição, validação e sanitização de campos de input. */
class InputField
{
    protected string $name;
    protected string $alias;
    protected mixed $value = null;

    protected bool $required = true;
    protected string $requiredErrorMessage = 'required';
    protected int $requiredErrorStatus = STS_BAD_REQUEST;

    protected bool $validated = false;
    protected array $ruleValidate = [];

    protected bool $sanitized = false;
    protected array $ruleSanitaze = [];
    protected mixed $valueSanitized = null;

    protected bool $preventTag = true;
    protected string $preventTagErrorMessage = 'preventTag';
    protected int $preventTagErrorStatus = STS_BAD_REQUEST;

    protected ?array $scapePrepare = [];

    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        $this->name = $name;
        $this->alias = $alias ?? $name;
        $this->value = str_get_var($value);
    }

    /** Lança uma Exception em nome do campo */
    function send($message, bool|int $status = false, array $prepare = []): never
    {
        if ($status === true) $status = STS_OK;
        if ($status === false || !is_httpStatus($status)) $status = STS_BAD_REQUEST;

        $prepare['name'] = $this->alias;
        $message = InputMessage::get($message ?? 'default') ?? $message;
        $message = prepare($message, $prepare);

        $send = [
            'field' => $this->name,
            'message' => $message,
        ];

        throw new Exception(json_encode($send), $status);
    }

    /** Define se o campo é obrigatório */
    function required(bool $required, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST): static
    {
        $this->required = $required;
        $this->requiredErrorMessage = $errorMessage ?? $this->requiredErrorMessage;
        $this->requiredErrorStatus = $errorStatus ?? $this->requiredErrorStatus;

        return $this;
    }

    /** Aplica as regras e retorna o valor do input */
    function get(): mixed
    {
        if (!$this->recived())
            return null;

        if (!$this->validated) {

            if ($this->preventTag && strip_tags($this->value) != $this->value)
                $this->send($this->preventTagErrorMessage, $this->preventTagErrorStatus);

            foreach ($this->ruleValidate as $rule)
                if (!array_shift($rule)($this->value))
                    $this->send(...$rule);

            $this->validated = true;
        }

        if (!$this->sanitized) {
            $this->valueSanitized = $this->value;

            foreach ($this->ruleSanitaze as $rule) {
                $rule = is_closure($rule) ? $rule : match ($rule) {
                    FILTER_SANITIZE_EMAIL => fn($v) => strtolower(filter_var($v, FILTER_SANITIZE_EMAIL)),
                    FILTER_SANITIZE_NUMBER_FLOAT => fn($v) => floatval(filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT)),
                    FILTER_SANITIZE_NUMBER_INT => fn($v) => intval(filter_var($v, FILTER_SANITIZE_NUMBER_INT)),
                    FILTER_SANITIZE_ENCODED,
                    FILTER_SANITIZE_ADD_SLASHES,
                    FILTER_SANITIZE_SPECIAL_CHARS,
                    FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    FILTER_SANITIZE_URL,
                    FILTER_UNSAFE_RAW => fn($v) => filter_var($v, $rule),
                    default => fn($v) => $v
                };
                $this->valueSanitized = $rule($this->valueSanitized);
            }

            if (is_null($this->scapePrepare) || count($this->scapePrepare))
                $this->valueSanitized = Prepare::scape($this->valueSanitized, $this->scapePrepare);

            $this->sanitized = true;
        }

        return $this->valueSanitized;
    }

    /** Define regras para validar um valor recebido */
    function validate(int|Closure|InputField $rule, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST)
    {
        $this->validated = false;

        if (is_closure($rule)) {
            $this->ruleValidate[] = [
                $rule,
                $errorMessage,
                $errorStatus
            ];
            return $this;
        }

        if (in_array($rule, [
            FILTER_VALIDATE_IP,
            FILTER_VALIDATE_MAC,
            FILTER_VALIDATE_URL,
            FILTER_VALIDATE_EMAIL,
            FILTER_VALIDATE_DOMAIN,
            FILTER_VALIDATE_REGEXP,
            FILTER_VALIDATE_BOOLEAN
        ])) {
            $this->ruleValidate[] = [
                fn($v) => filter_var($v, $rule),
                $errorMessage ?? $rule,
                $errorStatus
            ];
            return $this;
        }

        if (is_class($rule, InputField::class)) {
            $this->ruleValidate[] = [
                fn($v) => $v == $rule->value,
                $errorMessage ?? 'equal',
                $errorStatus,
                ['equal' => $rule->name]
            ];
            return $this;
        }

        if ($rule == FILTER_VALIDATE_INT) {
            $this->ruleValidate[] = [
                fn($v) => is_int($v),
                $errorMessage ?? $rule,
                $errorStatus
            ];
            return $this;
        }

        if ($rule == FILTER_VALIDATE_FLOAT) {
            $this->ruleValidate[] = [
                fn($v) => is_float($v),
                $errorMessage ?? $rule,
                $errorStatus
            ];
            return $this;
        }

        return $this;
    }

    /** Define regras para formatar um valor validado */
    function sanitize(int|Closure $rule)
    {
        $this->sanitized = false;
        $this->ruleSanitaze[] = $rule;
        return $this;
    }

    /** Verifica se o input foi recebido */
    function recived(): bool
    {
        $recived = !is_null($this->value);

        if (!$recived && $this->required)
            $this->send($this->requiredErrorMessage, $this->requiredErrorStatus);

        return $recived;
    }

    /** Define se o valor do input deve ser tratado com preventTag tags */
    function preventTag(bool $preventTag, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST): static
    {
        $this->preventTag = $preventTag;
        $this->preventTagErrorMessage = $errorMessage ?? $this->preventTagErrorMessage;
        $this->preventTagErrorStatus = $errorStatus ?? $this->preventTagErrorStatus;

        return $this;
    }

    /** Define quais tags prepare o campo deve escapar */
    function scapePrepare(bool|array $scapePrepare = true): static
    {
        if (is_bool($scapePrepare))
            $scapePrepare = $scapePrepare ? null : [];

        $this->scapePrepare = $scapePrepare;

        return $this;
    }
}
