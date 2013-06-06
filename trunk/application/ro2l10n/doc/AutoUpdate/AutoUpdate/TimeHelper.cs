using System;

namespace AutoUpdate
{
    public class TimeHelper
    {
        public int GMT;
        public DateTime tzero;

        public TimeHelper(int gmt)
        {
            this.GMT = gmt;
            tzero = new DateTime(1970, 1, 1, gmt, 0, 0, 0);
        }

        public string TimeToString(long time, string format)
        {
            if (format == "")
            {
                format = "yyyy-MM-dd HH:mm:ss";
            }
            TimeSpan ts = new TimeSpan(time * 10000000);
            DateTime res = this.tzero.Add(ts);
            return res.ToString(format);
        }
        public long StringToTime(string time)
        {
            string[] t_split = time.Split(new Char[] { '-', ' ', ':' });
            DateTime res = new DateTime(Convert.ToInt32(t_split[0]), Convert.ToInt32(t_split[1]), Convert.ToInt32(t_split[2]), Convert.ToInt32(t_split[3]), Convert.ToInt32(t_split[4]), Convert.ToInt32(t_split[5]), 0);
            return (long)Math.Round((res - this.tzero).TotalMilliseconds, MidpointRounding.AwayFromZero) / 1000;
        }
        public string NowTimeString(string format)
        {
            return DateTime.Now.ToString(format);
        }
        public long NowTimeStamp()
        {
            DateTime now = DateTime.Now;
            return (long)Math.Round((now - this.tzero).TotalMilliseconds, MidpointRounding.AwayFromZero) / 1000;
        }
        public long DayTimeStamp(int day)
        {
            DateTime now = DateTime.Now;
            now = new DateTime(now.Year, now.Month, now.Day, 0, 0, 0, 0);
            return (long)Math.Round((now - this.tzero).TotalMilliseconds + 86400000 * day, MidpointRounding.AwayFromZero) / 1000;
        }
    }
}
