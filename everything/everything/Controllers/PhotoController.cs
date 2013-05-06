using System;
using System.Collections.Generic;
using System.Data;
using System.Data.Entity;
using System.Drawing;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using everything.Models;

namespace everything.Controllers
{
    public class PhotoController : Controller
    {
        private PhotoDbContext db = new PhotoDbContext();

        //
        // GET: /Photo/

        public ActionResult Index()
        {
            //ViewData["MSG"] = "Hello from controller!!!";
            ViewBag.Title = "Фотогалерея";
            return View(db.Photos.ToList());
        }

        //
        // GET: /Photo/Details/5

        public ActionResult Details(int id = 0)
        {
            Photo photo = db.Photos.Find(id);
            ViewBag.Title = "Деталі";
            if (photo == null)
            {
                return HttpNotFound();
            }
            return View(photo);
        }

        //
        // GET: /Photo/Create

        public ActionResult Create()
        {
            ViewBag.Title = "Створити";
            return View();
        }

        //
        // POST: /Photo/Create

        [HttpPost]
        public ActionResult Create(Photo photo, IEnumerable<HttpPostedFileBase> fileUpload)
        {
            photo.ReleaseDate = DateTime.Now;
            string solDir = AppDomain.CurrentDomain.BaseDirectory + "Images\\";
            //string partUrl = Request.Url.Scheme + System.Uri.SchemeDelimiter + Request.Url.Host +
            //                             (Request.Url.IsDefaultPort ? "" : ":" + Request.Url.Port) + "/Images/";
            string partUrl = "/Images/";
            if (photo.Link != null)
            {
                photo.Link = partUrl + photo.DownloadImageFromUrl(photo);
            }
            if (fileUpload.First() != null)
            {
                photo.Link = partUrl + photo.DownloadImageFromClient(fileUpload, photo);
            }
            Image img1 = Image.FromFile(solDir + photo.Title);
            double ratio = photo.GetImgAspectRatio(img1)*100;
            Image img2 = img1.GetThumbnailImage(Convert.ToInt32(ratio), 100, null, IntPtr.Zero);
            img1.Dispose();
            img1 = null;
            string tmp = photo.SavePoster(img2, photo);
            img2.Dispose();
            img2 = null;
            photo.PrevLink = partUrl + "/Preview/" + photo.Title;
            if (ModelState.IsValid)
            {
                db.Photos.Add(photo);
                db.SaveChanges();
            }
            return RedirectToAction("Index", "Photo");
        }

        //
        // GET: /Photo/Edit/5

        public ActionResult Edit(int id = 0)
        {
            ViewBag.Title = "Редагувати";
            Photo photo = db.Photos.Find(id);
            if (photo == null)
            {
                return HttpNotFound();
            }
            return View(photo);
        }

        //
        // POST: /Photo/Edit/5

        [HttpPost]
        public ActionResult Edit(Photo photo)
        {
            if (ModelState.IsValid)
            {
                db.Entry(photo).State = EntityState.Modified;
                db.SaveChanges();
                return RedirectToAction("Index");
            }
            return View(photo);
        }

        //
        // GET: /Photo/Delete/5

        public ActionResult Delete(int id = 0)
        {
            ViewBag.Title = "Видалити";
            Photo photo = db.Photos.Find(id);
            if (photo == null)
            {
                return HttpNotFound();
            }
            return View(photo);
        }

        //
        // POST: /Photo/Delete/5

        [HttpPost, ActionName("Delete")]
        public ActionResult DeleteConfirmed(int id)
        {
            Photo photo = db.Photos.Find(id);
            ///////////////
            string solutionDir = HttpContext.Server.MapPath("~" + "/Images/");
            photo.DeleteImage(solutionDir + "/Preview/" + photo.Title);
            photo.DeleteImage(solutionDir + photo.Title);
            ///////////////
            db.Photos.Remove(photo);
            db.SaveChanges();
            return RedirectToAction("Index");
        }

        protected override void Dispose(bool disposing)
        {
            db.Dispose();
            base.Dispose(disposing);
        }
    }
}