<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'required',
            'finish_time' => 'required',
            'description' => 'required|max:255',
            'rests' => 'array|nullable',
            'rests.*.start' => 'nullable|date_format:H:i',
            'rests.*.finish' => 'nullable|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'finish_time.required' => '退勤時間を入力してください',
            'description.required' => '備考を記入してください',
            'description.max' => '備考は255文字以下で記入してください',
            'rests.*.start.date_format' => '休憩時間が不正な形式です',
            'rests.*.finish.date_format' => '休憩時間が不正な形式です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $finish = $this->input('finish_time');
            $rests = $this->input('rests', []);

            // ===========================
            // ① 出勤 > 退勤 のとき
            // ===========================
            if ($start && $finish && $start > $finish) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // ===========================
            // ② 休憩時間が勤務時間外のとき
            // ===========================
            foreach ($rests as $index => $rest) {
                $restStart = $rest['start'] ?? null;
                $restFinish = $rest['finish'] ?? null;

                // 休憩開始が出勤前 or 退勤後
                if ($restStart && $start && $restStart < $start) {
                    $validator->errors()->add("rests.$index.start", '休憩時間が勤務時間外です');
                }
                if ($restStart && $finish && $restStart > $finish) {
                    $validator->errors()->add("rests.$index.start", '休憩時間が勤務時間外です');
                }

                // 休憩終了が出勤前 or 退勤後
                if ($restFinish && $start && $restFinish < $start) {
                    $validator->errors()->add("rests.$index.finish", '休憩時間が勤務時間外です');
                }
                if ($restFinish && $finish && $restFinish > $finish) {
                    $validator->errors()->add("rests.$index.finish", '休憩時間が勤務時間外です');
                }

                // 休憩開始 > 休憩終了 のとき
                if ($restStart && $restFinish && $restStart > $restFinish) {
                    $validator->errors()->add("rests.$index.start", '休憩時間が不正です');
                }
            }

            // ===========================
            // ③ 備考未入力（既にrulesでrequiredだが念のため）
            // ===========================
            if (!$this->input('description')) {
                $validator->errors()->add('description', '備考を記入してください');
            }

            // ===========================
            // ④ 休憩時間の重複チェック
            // ===========================
            for ($i = 0; $i < count($rests); $i++) {
                $aStart = $rests[$i]['start'] ?? null;
                $aFinish = $rests[$i]['finish'] ?? null;

                if (!$aStart || !$aFinish) continue; // どちらか未入力ならスキップ

                for ($j = $i + 1; $j < count($rests); $j++) {
                    $bStart = $rests[$j]['start'] ?? null;
                    $bFinish = $rests[$j]['finish'] ?? null;

                    if (!$bStart || !$bFinish) continue;

                    // 重なっている場合（例：AがBの中に食い込む）
                    if (!($aFinish <= $bStart || $bFinish <= $aStart)) {
                        $validator->errors()->add("rests.$j.start", '他の休憩と重複しています');
                        $validator->errors()->add("rests.$i.start", '他の休憩と重複しています');
                    }
                }
            }
        });
    }
}

