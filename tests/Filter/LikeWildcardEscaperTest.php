<?php

use Webbizi\ListQuery\Filter\LikeWildcardEscaper;

test('it escapes percent wildcard', function (): void {
    expect(LikeWildcardEscaper::escape('100%'))->toBe('100\\%');
});

test('it escapes underscore wildcard', function (): void {
    expect(LikeWildcardEscaper::escape('user_name'))->toBe('user\\_name');
});

test('it escapes backslash', function (): void {
    expect(LikeWildcardEscaper::escape('path\\file'))->toBe('path\\\\file');
});

test('it escapes combined wildcards', function (): void {
    expect(LikeWildcardEscaper::escape('50% off_sale'))->toBe('50\\% off\\_sale');
});

test('it returns value unchanged when no wildcards', function (): void {
    expect(LikeWildcardEscaper::escape('normal value'))->toBe('normal value');
});
