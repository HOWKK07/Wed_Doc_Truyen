import os
import sys

# Thêm ffmpeg vào PATH cho chắc chắn
os.environ["PATH"] += os.pathsep + r"C:\ffmpeg\bin"

import whisper

with open("C:/xampp/htdocs/Wed_Doc_Truyen/python_path.txt", "w") as f:
    f.write(sys.executable)

if len(sys.argv) < 2:
    print("Usage: python generate_sub.py <audio_path>")
    sys.exit(1)

audio_path = sys.argv[1]
output_path = audio_path.rsplit('.', 1)[0] + ".vtt"

model = whisper.load_model("base")
result = model.transcribe(audio_path, language="vi")

# Tạo file VTT
with open(output_path, "w", encoding="utf-8") as f:
    f.write("WEBVTT\n\n")
    for seg in result["segments"]:
        start = seg["start"]
        end = seg["end"]
        text = seg["text"].strip()
        # Định dạng thời gian VTT
        def sec2vtt(sec):
            h = int(sec // 3600)
            m = int((sec % 3600) // 60)
            s = sec % 60
            return f"{h:02}:{m:02}:{s:06.3f}"  # Đúng chuẩn VTT, dùng dấu chấm
        f.write(f"{sec2vtt(start)} --> {sec2vtt(end)}\n{text}\n\n")
print("OK")