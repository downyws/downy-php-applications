using System;
using System.IO;
using System.Net;
using System.Windows.Forms;
using System.Collections.Generic;

namespace AutoUpdate
{
    class FileHelper
    {
        public TimeHelper TH;
        public Random R;
        public ProgressBar PB;
        public string PATCH_PATH = Application.StartupPath + "/hotzeal/patch";
        public string BASE_PATH = Application.StartupPath + "/hotzeal/autoupdate/";
        public string DOWN_PATH = Application.StartupPath + "/hotzeal/autoupdate/temp/";
        public string BACK_PATH = Application.StartupPath + "/hotzeal/autoupdate/back/";
        public int BACK_LIMIT = 3;

        public FileHelper(ProgressBar pb)
        {
            // 初始化
            this.TH = new TimeHelper(8);
            this.R = new Random();
            this.PB = pb;

            // 目录检测
            this.DirSafe(this.PATCH_PATH);
            this.DirSafe(this.DOWN_PATH);
            this.DirSafe(this.BACK_PATH);

            // 清空下载目录
            string[] files = Directory.GetFiles(this.DOWN_PATH);
            foreach (string file in files)
            {
                File.Delete(file);
            }
        }

        public void DirSafe(string path)
        {
            if (!Directory.Exists(path) && !File.Exists(path))
            {
                string file_name = Path.GetFileName(path);
                if (file_name.IndexOf(".") != -1)
                {
                    path = path.Replace(file_name, "");
                }
                Directory.CreateDirectory(path);
            }
        }

        public bool Move(string old_path, string new_path)
        {
            if (File.Exists(old_path))
            {
                this.DirSafe(new_path);
                File.Move(old_path, new_path);
                return true;
            }
            return false;
        }

        public string Back(string path)
        {
            if (File.Exists(path))
            {
                string file_name = Path.GetFileName(path);
                string file_path = path.Replace(Application.StartupPath, "");
                file_path = file_path.Replace(file_name, "");
                string back_path = this.BACK_PATH + file_path + "/" + file_name + ".back." + this.TH.NowTimeStamp().ToString();

                this.DirSafe(this.BACK_PATH + file_path);
                File.Move(path, back_path);

                // 检查备份数
                List<string> back_list = new List<string>();
                string[] files = Directory.GetFiles(this.BACK_PATH + file_path);
                string temp_file_name = "";
                string reg = file_name + ".back.";
                foreach (var file in files)
                {
                    temp_file_name = Path.GetFileName(file);
                    if (temp_file_name.IndexOf(reg) >= 0)
                    {
                        back_list.Add(file);
                    }
                }
                back_list.Sort();
                int i = 0;
                while (back_list.Count > this.BACK_LIMIT)
                {
                    File.Delete(back_list[i++]);
                    back_list.RemoveAt(i);
                }

                return back_path;
            }
            return "";
        }

        public bool UpdateFile(string url, string path)
        {
            // 检查目录
            this.DirSafe(path);

            // 备份文件
            this.Back(path);

            // 下载
            string temp_path = this.Download(url);
            File.Move(temp_path, path);
            return true;
        }

        public string Download(string url)
        {
            string path = this.DOWN_PATH + this.TH.NowTimeStamp().ToString() + "_" + Convert.ToInt32(this.R.NextDouble() * 1000).ToString() + ".tmp";
            if (File.Exists(path))
            {
                File.Delete(path);
            }

            HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(url);
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();
            Stream inStream = response.GetResponseStream();
            Stream outStream = new FileStream(path, FileMode.Create);

            long totalBytes = response.ContentLength;
            long totalDownloadedByte = 0;
            byte[] by = new byte[1024];
            int osize = inStream.Read(by, 0, (int)by.Length);
            float percent = 0;
            this.PB.Maximum = (int)totalBytes;

            while (osize > 0)
            {
                totalDownloadedByte = osize + totalDownloadedByte;
                Application.DoEvents();
                outStream.Write(by, 0, osize);

                this.PB.Value = (int)totalDownloadedByte;

                osize = inStream.Read(by, 0, (int)by.Length);

                percent = (float)totalDownloadedByte / (float)totalBytes * 100;
                Application.DoEvents();
            }
            outStream.Close();
            inStream.Close();

            return path;
        }
    }
}
