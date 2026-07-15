@php
    /** @var \App\Models\Message $message */
    $message = $message;
    $isSent = $isSent;
    $bubbleClass = $bubbleClass;
    $currentUserId = $currentUserId;
    $readState = $isSent ? $message->readStateFor($currentUserId) : null;
    $isGroup = $message->conversation?->is_group ?? false;
    $isFirstInCluster = $loop ?? null;
@endphp

@if($showDateSeparator)
    <div class="wa-date-sep" data-date="{{ $currentDate }}">
        <span>
            @php
                $d = \Carbon\Carbon::parse($currentDate);
                $today = \Carbon\Carbon::today();
                $yesterday = \Carbon\Carbon::yesterday();
                if ($d->isSameDay($today)) echo 'اليوم';
                elseif ($d->isSameDay($yesterday)) echo 'أمس';
                elseif ($d->gt($today->copy()->subDays(7))) echo $d->locale('ar')->dayName;
                else echo $d->locale('ar')->translatedFormat('j F Y');
            @endphp
        </span>
    </div>
@endif

<div class="wa-msg {{ $bubbleClass }} {{ $loop && $loop->first ? 'leading' : 'followed' }}" data-message-id="{{ $message->id }}" data-date="{{ $message->created_at->toDateString() }}">
    <div class="wa-bubble">
        @if(!$isSent && $isGroup && ($loop && $loop->first || ($loop && $loop->index > 0 && $message->user_id !== $message->conversation->messages[$loop->index - 1]->user_id)))
            <span class="wa-bubble-sender">{{ $message->user->name }}</span>
        @endif

        @if($message->attachments->isNotEmpty())
            <div class="wa-attach">
                @foreach($message->attachments as $att)
                    @if($att->is_image)
                        <a href="{{ $att->url }}" data-lightbox="chat-{{ $message->id }}" data-title="{{ $att->original_name }}" class="wa-attach-img" title="{{ $att->original_name }}">
                            <img src="{{ $att->url }}" alt="{{ $att->original_name }}" loading="lazy">
                        </a>
                    @elseif($att->is_video)
                        <div class="wa-attach-video">
                            <div class="wa-attach-icon"><i class="fas fa-video"></i></div>
                            <div class="wa-attach-info">
                                <div class="wa-attach-name">{{ $att->original_name }}</div>
                                <div class="wa-attach-size">{{ $att->human_size }}</div>
                            </div>
                            <a href="{{ route('chat.attachment.download', $att) }}" class="wa-attach-dl" target="_blank" title="تحميل"><i class="fas fa-download"></i></a>
                        </div>
                    @elseif($att->is_audio)
                        <div class="wa-attach-audio">
                            <div class="wa-attach-icon"><i class="fas fa-music"></i></div>
                            <div class="wa-attach-info">
                                <div class="wa-attach-name">{{ $att->original_name }}</div>
                                <div class="wa-attach-size">{{ $att->human_size }}</div>
                            </div>
                            <a href="{{ route('chat.attachment.download', $att) }}" class="wa-attach-dl" target="_blank" title="تحميل"><i class="fas fa-download"></i></a>
                        </div>
                    @else
                        <a href="{{ route('chat.attachment.download', $att) }}" class="wa-attach-file" target="_blank">
                            <div class="wa-attach-icon"><i class="fas {{ $att->icon }}"></i></div>
                            <div class="wa-attach-info">
                                <div class="wa-attach-name">{{ $att->original_name }}</div>
                                <div class="wa-attach-size">{{ $att->human_size }}</div>
                            </div>
                            <div class="wa-attach-dl"><i class="fas fa-download"></i></div>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

        @if(!empty($message->content))
            <div class="wa-bubble-text">{{ $message->content }}</div>
        @endif

        <span class="wa-bubble-meta">
            <span class="wa-msg-time">{{ $message->created_at->format('H:i') }}</span>
            @if($isSent)
                @if($readState === 'read')
                    <i class="fas fa-check-double wa-tick read"></i>
                @elseif($readState === 'delivered')
                    <i class="fas fa-check-double wa-tick delivered"></i>
                @else
                    <i class="fas fa-check wa-tick sent-only"></i>
                @endif
            @elseif($message->attachments->isNotEmpty())
                <i class="fas fa-paperclip" style="font-size:10px;opacity:0.6;"></i>
            @endif
        </span>
    </div>
</div>
