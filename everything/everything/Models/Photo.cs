using System;
using System.Collections.Generic;
using System.Data.Entity;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Net;
using System.Web;

namespace everything.Models
{
    public class Photo
    {
        public int Id { get; set; }
        public string Title { get; set; }
        public DateTime ReleaseDate { get; set; }
        public string Link { get; set; }
        public string PrevLink { get; set; }

        public string DownloadImageFromUrl(Photo photo)
        {
            string solutionDir = AppDomain.CurrentDomain.BaseDirectory + "Images\\";
            string format = photo.GetLast(photo.Link, 4);
            int count = 0;
            while (count < 25)
            {
                try
                {
                    WebClient client = new WebClient();
                    byte[] data = client.DownloadData(photo.Link);
                    if (data != null && data.Length > 10)
                    {
                        using (FileStream rw = new FileStream(solutionDir + photo.Title + format, FileMode.CreateNew))
                        {
                            rw.Write(data, 0, data.Length);
                        }
                    }
                    photo.Title = photo.Title + format;
                    return photo.Title;
                }
                catch
                {
                    photo.Title = photo.Title + "1";
                    count++;
                }
            }
            return photo.Title;
        }

        public string DownloadImageFromClient(IEnumerable<HttpPostedFileBase> fileUpload, Photo photo)
        {
            string path = AppDomain.CurrentDomain.BaseDirectory + "Images/";
            foreach (var file in fileUpload)
            {
                if (file == null) continue;
                photo.Title = photo.Title + photo.GetLast(file.FileName, 4);
                if (photo.Title != null) file.SaveAs(Path.Combine(path, photo.Title));
                return photo.Title;
            }
            return "Error!";
        }

        public string SavePoster(Image img, Photo photo)
        {
            string solutionDir = AppDomain.CurrentDomain.BaseDirectory + "Images\\Preview\\";
            img.Save(solutionDir + photo.Title);
            return null;
        }

        public double GetImgAspectRatio(Image img)
        {
            double width = img.Width;
            double height = img.Height;
            double res = (width)/(height);
            return res;
        }

        /*public Image ScaleImage(Image image, int maxHeight)
        {
            string solutionDir = AppDomain.CurrentDomain.BaseDirectory + "Images/Preview";
            var ratio = (double)maxHeight / image.Height;

            var newWidth = (int)(image.Width * ratio);
            var newHeight = (int)(image.Height * ratio);

            var newImage = new Bitmap(newWidth, newHeight);
            using (var g = Graphics.FromImage(newImage))
            {
                g.DrawImage(image, 0, 0, newWidth, newHeight);
            }
            return newImage;
        } */

        public string DeleteImage(string targetPath)
        {
            try
            {
                if (targetPath != null)
                {
                    File.Delete(targetPath);
                }
            }
            catch
            {
                //return exDelete.ToString();
                return null;
            }
            return null;
        }

        public string GetLast(string s, int tailLength)
        {
            if (tailLength >= s.Length)
            {
                return s;
            }
            string sub = s.Substring(s.Length - tailLength);
            return sub;
        }
    }

    public class PhotoDbContext : DbContext
    {
        public DbSet<Photo> Photos { get; set; }
    }
}