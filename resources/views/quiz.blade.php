<!DOCTYPE html>
<html>
<head>
    <title>Notes to Quiz</title>
    <style>
        body { font-family: sans-serif; max-width: 720px; margin: 40px auto; padding: 0 16px; }
        textarea { width: 100%; height: 150px; }
        .ok { color: #1a7f37; } .bad { color: #cf222e; } .err { color: #cf222e; }
        .q { margin: 16px 0; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
<h1>Notes to Quiz</h1>

<form method="POST" action="/generate" enctype="multipart/form-data"
      onsubmit="this.querySelector('button').innerText='Thinking... (can take a minute)'; this.querySelector('button').disabled=true;">
    @csrf
    <p><strong>Upload a PDF or TXT file</strong><br><input type="file" name="file" accept=".pdf,.txt"></p>
    <p>Or paste text:</p>
    <textarea name="notes" placeholder="Paste your notes here">{{ old('notes') }}</textarea>
    @error('notes') <p class="err">{{ $message }}</p> @enderror
    @error('file') <p class="err">{{ $message }}</p> @enderror

    <p>Question type:
        <label><input type="radio" name="type" value="mcq" checked> Multiple choice</label>
        <label><input type="radio" name="type" value="fill"> Fill in the blanks</label>
    </p>
    <p>Number of questions: <input type="number" name="n" value="3" min="1" max="5"></p>
    <button type="submit">Generate quiz</button>
</form>

@if ($quiz && !$result)
    <hr>
    <form method="POST" action="/submit">
        @csrf
        @foreach ($quiz as $i => $q)
            <div class="q">
                <strong>{{ $i + 1 }}. {{ $q['question'] }}</strong>
                @if ($type === 'mcq')
                    @foreach ($q['options'] as $k => $opt)
                        <div><label>
                            <input type="radio" name="answers[{{ $i }}]" value="{{ $k }}" required> {{ $opt }}
                        </label></div>
                    @endforeach
                @else
                    <div><input type="text" name="answers[{{ $i }}]" required autocomplete="off"></div>
                @endif
            </div>
        @endforeach
        <button type="submit">Submit answers</button>
    </form>
@endif

@if ($quiz && $result)
    <hr>
    <h2>Score: {{ $result['score'] }} / {{ $result['total'] }}</h2>
    @foreach ($quiz as $i => $q)
        @php $row = $result['rows'][$i]; @endphp
        <div class="q">
            <strong>{{ $i + 1 }}. {{ $q['question'] }}</strong>
            @if ($row['correct'])
                <p class="ok">Correct. Your answer: {{ $row['yours'] }}</p>
            @else
                <p class="bad">Incorrect. Your answer: {{ $row['yours'] ?: '(blank)' }}</p>
                <p>Right answer: {{ $row['right'] }}</p>
            @endif
            <small>{{ $q['explanation'] ?? '' }}</small>
        </div>
    @endforeach
@endif
</body>
</html>