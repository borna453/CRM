<?php

namespace App\Models;

use App\Models\Contracts\TenantContract;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;


/**
 * 
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property string|null $from
 * @property string|null $to
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $subject
 * @property string|null $text_body
 * @property string|null $html_body
 * @property string|null $raw_body
 * @property string|null $sent_debug_info
 * @property string|null $mailer
 * @property string|null $mailable_subject_type
 * @property string|null $mailable_subject_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $mailableSubject
 * @property-read \App\Models\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|Email newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Email newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Email query()
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereBcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereCc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereHtmlBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereMailableSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereMailableSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereMailer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereRawBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereSentDebugInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereTextBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Email extends \Cloudmazing\FilamentEmailLog\Models\Email implements TenantContract
{
    use BelongsToTenant;
}
